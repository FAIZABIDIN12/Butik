<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\Account;
use App\Models\LedgerEntry;
use App\Models\Setting;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\MonthlyBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;

class PenjualanController extends Controller
{
    public function index()
    {
        return view('penjualan.index');
    }

    public function data()
    {
        $penjualan = Penjualan::with('member')->orderBy('id_penjualan', 'desc')->get();

        return datatables()
            ->of($penjualan)
            ->addIndexColumn()
            ->addColumn('total_item', function ($penjualan) {
                return format_uang($penjualan->total_item);
            })
            ->addColumn('total_harga', function ($penjualan) {
                return 'Rp. ' . format_uang($penjualan->total_harga);
            })
            ->addColumn('bayar', function ($penjualan) {
                return 'Rp. ' . format_uang($penjualan->bayar);
            })
            ->addColumn('tanggal', function ($penjualan) {
                return tanggal_indonesia($penjualan->created_at, false);
            })
            ->addColumn('kode_member', function ($penjualan) {
                $member = $penjualan->member->kode_member ?? '';
                return '<span class="label label-success">' . $member . '</spa>';
            })
            ->editColumn('diskon', function ($penjualan) {
                return $penjualan->diskon . '%';
            })
            ->editColumn('kasir', function ($penjualan) {
                return $penjualan->user->name ?? '';
            })
            ->addColumn('aksi', function ($penjualan) {
                return '
                <div class="btn-group">
                    <button onclick="showDetail(`' . route('penjualan.show', $penjualan->id_penjualan) . '`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                    <button onclick="deleteData(`' . route('penjualan.destroy', $penjualan->id_penjualan) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi', 'kode_member'])
            ->make(true);
    }

    public function create()
    {
        $penjualan = new Penjualan();
        $penjualan->id_member = null;
        $penjualan->total_item = 0;
        $penjualan->total_harga = 0;
        $penjualan->diskon = 0;
        $penjualan->bayar = 0;
        $penjualan->diterima = 0;
        $penjualan->id_user = auth()->id();
        $penjualan->save();

        session(['id_penjualan' => $penjualan->id_penjualan]);
        return redirect()->route('transaksi.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_member' => 'nullable|exists:member,id_member',
            'total_item' => 'required|integer',
            'diskon' => 'required|numeric',
            'bayar' => 'required|numeric',
            'diterima' => 'required|numeric',
            'total' => 'required|numeric',
            'metode_pembayaran' => 'required|in:tunai,non_tunai',
        ]);

        // Temukan penjualan yang ada
        $penjualan = Penjualan::findOrFail($request->id_penjualan);

        // Terapkan pembulatan pada nilai yang relevan
        $roundedBayar = $this->roundToNearestThousand($request->bayar);
        $totalSetelahDiskon = $this->roundToNearestThousand($request->total - ($request->total * $request->diskon / 100));

        $penjualan->id_member = $request->id_member;
        $penjualan->total_item = $request->total_item;
        $penjualan->total_harga = $this->roundToNearestThousand($request->total); // Pembulatan total harga
        $penjualan->diskon = $request->diskon;
        $penjualan->bayar = $roundedBayar;
        $penjualan->diterima = $this->roundToNearestThousand($request->diterima); // Pembulatan nilai diterima
        $penjualan->metode_pembayaran = $request->metode_pembayaran;
        $penjualan->update();

        // Update detail penjualan dan stok produk
        $detail = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
        $totalHPP = 0; // Initialize total HPP (cost of goods sold)

        foreach ($detail as $item) {
            $item->diskon = $request->diskon;
            $item->update();

            // Update stock of product
            $produk = Produk::find($item->id_produk);
            $produk->stok -= $item->jumlah;
            $produk->update();

            // Calculate total cost for the item (assuming `harga_beli` is the cost price)
            $totalHPP += $produk->harga_beli * $item->jumlah;
        }

        // Simpan transaksi cashflow
        $transaction = Transaction::create([
            'date' => now(),
            'description' => 'Penjualan barang: ' . $penjualan->id_penjualan,
            'transaction_type' => 'in',
            'amount' => $totalSetelahDiskon,
            'nominal' => $totalSetelahDiskon,
            'category_code' => '001',
            'current_balance' => 0,
            'user_id' => Auth::id(),
            'transaction_at' => now(),
        ]);

        // Simpan atau update pembayaran
        $payment = Payment::where('metode_pembayaran', $request->metode_pembayaran)->first();
        if ($payment) {
            $payment->amount += $roundedBayar; // Gunakan nilai bayar yang sudah dibulatkan
            $payment->save();
        } else {
            Payment::create([
                'penjualan_id' => $penjualan->id_penjualan,
                'amount' => $roundedBayar,
                'metode_pembayaran' => $request->metode_pembayaran,
            ]);
        }

        // Update saldo akun Pendapatan HPP BD (Akun 401)
        $pendapatanAccount = Account::where('code', '401')->first();
        if ($pendapatanAccount) {
            $pendapatanAccount->current_balance += $totalSetelahDiskon;
            $pendapatanAccount->save();

            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => $pendapatanAccount->code,
                'entry_date' => now(),
                'entry_type' => 'credit',
                'amount' => $totalSetelahDiskon,
                'balance' => $pendapatanAccount->current_balance,
            ]);

            $this->updateMonthlyBalance($pendapatanAccount->code, $totalSetelahDiskon);
        }

        // Update saldo akun Kas Butik (Akun 102)
        $kasButikAccount = Account::where('code', '102')->first();
        if ($kasButikAccount) {
            $kasButikAccount->current_balance += $totalSetelahDiskon;
            $kasButikAccount->save();

            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => $kasButikAccount->code,
                'entry_date' => now(),
                'entry_type' => 'debit',
                'amount' => -$totalSetelahDiskon,
                'balance' => $kasButikAccount->current_balance,
            ]);

            $this->updateMonthlyBalance($kasButikAccount->code, $totalSetelahDiskon);
        }

        // Update saldo akun Laba Berjalan (Akun 203)
        $labaBerjalanAccount = Account::where('code', '203')->first();
        if ($labaBerjalanAccount) {
            $labaBerjalanAccount->current_balance += $totalSetelahDiskon;
            $labaBerjalanAccount->save();

            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => $labaBerjalanAccount->code,
                'entry_date' => now(),
                'entry_type' => 'credit',
                'amount' => $totalSetelahDiskon,
                'balance' => $labaBerjalanAccount->current_balance,
            ]);

            $this->updateMonthlyBalance($labaBerjalanAccount->code, $totalSetelahDiskon);
        }

        return redirect()->route('transaksi.selesai')->with('success', 'Penjualan berhasil disimpan dan cashflow diperbarui.');
    }


    private function roundToNearestThousand($amount)
    {
        $remainder = $amount % 1000;

        if ($remainder > 500) {
            return ceil($amount / 1000) * 1000; // Bulatkan ke atas
        } elseif ($remainder === 500) {
            return $amount; // Tidak dibulatkan jika sisa tepat 500
        } else {
            return floor($amount / 1000) * 1000; // Bulatkan ke bawah
        }
    }
    // Fungsi untuk memperbarui saldo bulanan
    private function updateMonthlyBalance($accountCode, $amount)
    {
        $currentMonthYear = now()->format('Y-m'); // Format MM-YYYY

        // Cari saldo bulanan berdasarkan kode akun dan bulan
        $monthlyBalance = MonthlyBalance::where('account_code', $accountCode)
            ->where('month', $currentMonthYear)
            ->first();

        if ($monthlyBalance) {
            // Jika catatan bulan ini sudah ada, tambahkan jumlahnya
            $monthlyBalance->balance += $amount;
            $monthlyBalance->save();
        } else {
            // Jika catatan bulan ini belum ada, buat catatan baru
            MonthlyBalance::create([
                'account_code' => $accountCode,
                'month' => $currentMonthYear,
                'balance' => $amount,
            ]);
        }
    }


    public function show($id)
    {
        $detail = PenjualanDetail::with('produk')->where('id_penjualan', $id)->get();

        return datatables()
            ->of($detail)
            ->addIndexColumn()
            ->addColumn('kode_produk', function ($detail) {
                return '<span class="label label-success">' . $detail->produk->kode_produk . '</span>';
            })
            ->addColumn('nama_produk', function ($detail) {
                return $detail->produk->nama_produk;
            })
            ->addColumn('harga_jual', function ($detail) {
                return 'Rp. ' . format_uang($detail->harga_jual);
            })
            ->addColumn('jumlah', function ($detail) {
                return format_uang($detail->jumlah);
            })
            ->addColumn('subtotal', function ($detail) {
                return 'Rp. ' . format_uang($detail->subtotal);
            })
            ->rawColumns(['kode_produk'])
            ->make(true);
    }

    public function destroy($id)
    {
        $penjualan = Penjualan::find($id);
        $detail    = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
        foreach ($detail as $item) {
            $produk = Produk::find($item->id_produk);
            if ($produk) {
                $produk->stok += $item->jumlah;
                $produk->update();
            }

            $item->delete();
        }

        $penjualan->delete();

        return response(null, 204);
    }

    public function selesai()
    {
        $setting = Setting::first();

        return view('penjualan.selesai', compact('setting'));
    }

    public function notaKecil()
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find(session('id_penjualan'));
        if (! $penjualan) {
            abort(404);
        }
        $detail = PenjualanDetail::with('produk')
            ->where('id_penjualan', session('id_penjualan'))
            ->get();

        return view('penjualan.nota_kecil', compact('setting', 'penjualan', 'detail'));
    }

    public function notaBesar()
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find(session('id_penjualan'));
        if (! $penjualan) {
            abort(404);
        }
        $detail = PenjualanDetail::with('produk')
            ->where('id_penjualan', session('id_penjualan'))
            ->get();

        $pdf = PDF::loadView('penjualan.nota_besar', compact('setting', 'penjualan', 'detail'));
        $pdf->setPaper(0, 0, 609, 440, 'potrait');
        return $pdf->stream('Transaksi-' . date('Y-m-d-his') . '.pdf');
    }
}
