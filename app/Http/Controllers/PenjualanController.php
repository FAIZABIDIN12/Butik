<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\Account;
use App\Models\LedgerEntry;
use App\Models\Setting;
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
                return 'Rp. '. format_uang($penjualan->total_harga);
            })
            ->addColumn('bayar', function ($penjualan) {
                return 'Rp. '. format_uang($penjualan->bayar);
            })
            ->addColumn('tanggal', function ($penjualan) {
                return tanggal_indonesia($penjualan->created_at, false);
            })
            ->addColumn('kode_member', function ($penjualan) {
                $member = $penjualan->member->kode_member ?? '';
                return '<span class="label label-success">'. $member .'</spa>';
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
                    <button onclick="showDetail(`'. route('penjualan.show', $penjualan->id_penjualan) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                    <button onclick="deleteData(`'. route('penjualan.destroy', $penjualan->id_penjualan) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
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
        // Validasi input
        $request->validate([
            'id_member' => 'nullable|exists:members,id', // Validasi untuk member
            'total_item' => 'required|integer',
            'diskon' => 'required|numeric',
            'bayar' => 'required|numeric',
            'total' => 'required|numeric', // Validasi total
        ]);
    
        // Temukan penjualan yang ada
        $penjualan = Penjualan::findOrFail($request->id_penjualan);
        $penjualan->id_member = $request->id_member;
        $penjualan->total_item = $request->total_item;
        $penjualan->total_harga = $request->total;
        $penjualan->diskon = $request->diskon;
        $penjualan->bayar = $request->bayar;
        $penjualan->diterima = $request->diterima; // Diterima sesuai input
        $penjualan->update();
    
        // Update detail penjualan dan stok produk
        $detail = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
        foreach ($detail as $item) {
            $item->diskon = $request->diskon;
            $item->update();
    
            $produk = Produk::find($item->id_produk);
            $produk->stok -= $item->jumlah;
            $produk->update();
        }
    
          // Ambil kategori dan akun terkait untuk cashflow
    $cashflowAmount = $request->total; // Total penjualan
    $cashflowCategoryCode = '202'; // Kode kategori untuk penjualan

    // Simpan transaksi cashflow
    $transaction = Transaction::create([
        'date' => now(),
        'description' => 'Penjualan barang: ' . $penjualan->id_penjualan,
        'transaction_type' => 'in', // Transaksi masuk
        'amount' => $cashflowAmount,
        'nominal' => $cashflowAmount,
        'category_code' => $cashflowCategoryCode,
        'current_balance' => 0, // Atur sesuai kebutuhan
        'user_id' => Auth::id(), // Jika ada relasi dengan pengguna
        'transaction_at' => now(), // Set the transaction_at value
    ]);

    // Update saldo akun Pendapatan HPP BD (Akun 103)
    $pendapatanAccount = Account::where('code', '103')->first(); // Ambil akun Pendapatan HPP BD
    if ($pendapatanAccount) {
        $pendapatanAccount->current_balance += $request->total; // Tambah saldo Pendapatan HPP BD
        $pendapatanAccount->save();

        // Buat entry untuk ledger akun Pendapatan HPP BD
        LedgerEntry::create([
            'transaction_id' => $transaction->id,
            'account_code' => $pendapatanAccount->code,
            'entry_date' => now(),
            'entry_type' => 'credit',
            'amount' => $request->total,
            'balance' => $pendapatanAccount->current_balance, // Saldo setelah transaksi
        ]);

        // Update saldo bulanan di Monthly Balance untuk akun Pendapatan HPP BD
        $this->updateMonthlyBalance($pendapatanAccount->code, $request->total);
    }

    // Update saldo akun Kas Butik (Akun 100)
    $kasButikAccount = Account::where('code', '100')->first(); // Ambil akun Kas Butik
    if ($kasButikAccount) {
        $kasButikAccount->current_balance += $request->total; // Tambah saldo Kas Butik
        $kasButikAccount->save();

        // Buat entry untuk ledger akun Kas Butik
        LedgerEntry::create([
            'transaction_id' => $transaction->id,
            'account_code' => $kasButikAccount->code,
            'entry_date' => now(),
            'entry_type' => 'debit',
            'amount' => -$request->total, // Negative untuk debit
            'balance' => $kasButikAccount->current_balance, // Saldo setelah transaksi
        ]);

        // Update saldo bulanan di Monthly Balance untuk akun Kas Butik
        $this->updateMonthlyBalance($kasButikAccount->code, $request->total);
    }

    return redirect()->route('transaksi.selesai')->with('success', 'Penjualan berhasil disimpan dan cashflow diperbarui.');
}
    
    // Fungsi untuk memperbarui saldo bulanan
    private function updateMonthlyBalance($accountCode, $amount)
    {
        $currentMonthYear = now()->format('m-Y'); // Format MM-YYYY
    
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
                return '<span class="label label-success">'. $detail->produk->kode_produk .'</span>';
            })
            ->addColumn('nama_produk', function ($detail) {
                return $detail->produk->nama_produk;
            })
            ->addColumn('harga_jual', function ($detail) {
                return 'Rp. '. format_uang($detail->harga_jual);
            })
            ->addColumn('jumlah', function ($detail) {
                return format_uang($detail->jumlah);
            })
            ->addColumn('subtotal', function ($detail) {
                return 'Rp. '. format_uang($detail->subtotal);
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
        $pdf->setPaper(0,0,609,440, 'potrait');
        return $pdf->stream('Transaksi-'. date('Y-m-d-his') .'.pdf');
    }
}
