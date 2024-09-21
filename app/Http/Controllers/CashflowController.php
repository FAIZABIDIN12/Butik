<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use App\Models\Category;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class CashflowController extends Controller
{
    public function index()
    {
        $cashflows = Cashflow::all(); // Ambil semua data cashflow
        $categories = Category::all(); // Ambil semua kategori
    
        // Inisialisasi saldo
        $currentBalance = 0;
    
        // Loop melalui setiap cashflow untuk menghitung saldo
        foreach ($cashflows as $cashflow) {
            if ($cashflow->transaction_type == 'in') {
                $currentBalance += $cashflow->amount; // Tambah jika uang masuk
            } elseif ($cashflow->transaction_type == 'out') {
                $currentBalance -= $cashflow->amount; // Kurang jika uang keluar
            }
            // Simpan saldo saat ini di setiap cashflow
            $cashflow->current_balance = $currentBalance;
        }
    
        return view('cashflows.index', compact('cashflows', 'categories')); // Kirim data ke view
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'date' => 'required|date',
            'description' => 'required|string',
            'transaction_type' => 'required|in:in,out',
            'amount' => 'required|numeric',
            'category_code' => 'required|string',
        ]);
    
        // Ambil kategori
        $category = Category::where('code', $request->category_code)->first();
    
        if ($category) {
            $debitAccount = $category->debitAccount;
            $creditAccount = $category->creditAccount;
    
            // Perbarui saldo akun berdasarkan tipe transaksi
            if ($request->transaction_type === 'in') {
                // Transaksi masuk: tambah ke debit (aset, revenue), kurangi dari kredit (liability, expense)
                if ($debitAccount && in_array($debitAccount->position, ['asset', 'revenue'])) {
                    $debitAccount->current_balance += $request->amount;
                    $debitAccount->save();
                }
                if ($creditAccount && in_array($creditAccount->position, ['liability', 'expense'])) {
                    $creditAccount->current_balance -= $request->amount;
                    $creditAccount->save();
                }
            } elseif ($request->transaction_type === 'out') {
                // Transaksi keluar: kurangi dari debit (aset, expense), tambah ke kredit (liability, revenue)
                if ($debitAccount && in_array($debitAccount->position, ['asset', 'expense'])) {
                    $debitAccount->current_balance -= $request->amount;
                    $debitAccount->save();
                }
                if ($creditAccount && in_array($creditAccount->position, ['liability', 'revenue'])) {
                    $creditAccount->current_balance += $request->amount;
                    $creditAccount->save();
                }
            }
        }
    
        // Ambil saldo cashflow sebelumnya
        $previousBalance = Cashflow::orderBy('date', 'desc')->first()->current_balance ?? 0;
    
        // Hitung saldo baru di cashflow berdasarkan tipe transaksi
        if ($request->transaction_type == 'in') {
            $newBalance = $previousBalance + $request->amount;
        } else {
            $newBalance = $previousBalance - $request->amount;
        }
    
        // Simpan transaksi cashflow dengan saldo terbaru
        Cashflow::create([
            'date' => $request->date,
            'description' => $request->description,
            'transaction_type' => $request->transaction_type,
            'amount' => $request->amount,
            'category_code' => $request->category_code,
            'current_balance' => $newBalance,
        ]);
    
        return redirect()->route('cashflows.index')->with('success', 'Transaksi berhasil ditambahkan.');
    }
    

    public function edit($id)
    {
        $cashflow = Cashflow::findOrFail($id);
        $categories = Category::all();
        return view('cashflows.edit', compact('cashflow', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'description' => 'required|string',
            'transaction_type' => 'required|in:in,out',
            'amount' => 'required|numeric',
        ]);

        $cashflow = Cashflow::findOrFail($id);
        $previousAmount = $cashflow->amount;
        $previousTransactionType = $cashflow->transaction_type;

        // Update cashflow data
        $cashflow->update($request->only(['description', 'transaction_type', 'amount', 'category_code']));

        // Update saldo akun sesuai dengan perubahan
        $category = $cashflow->category;
        if ($category) {
            $debitAccount = $category->debitAccount;
            $creditAccount = $category->creditAccount;

            // Jika ada perubahan di amount atau transaction_type, kita update saldo akun terkait
            if ($previousTransactionType === 'in') {
                // Rollback saldo lama
                if ($debitAccount && $debitAccount->position === 'asset') {
                    $debitAccount->current_balance -= $previousAmount;
                    $debitAccount->save();
                }
            } elseif ($previousTransactionType === 'out') {
                if ($debitAccount && $debitAccount->position === 'asset') {
                    $debitAccount->current_balance += $previousAmount;
                    $debitAccount->save();
                }
            }

            // Apply saldo baru
            if ($request->transaction_type === 'in') {
                if ($debitAccount && $debitAccount->position === 'asset') {
                    $debitAccount->current_balance += $request->amount;
                    $debitAccount->save();
                }
            } elseif ($request->transaction_type === 'out') {
                if ($debitAccount && $debitAccount->position === 'asset') {
                    $debitAccount->current_balance -= $request->amount;
                    $debitAccount->save();
                }
            }
        }

        return redirect()->route('cashflows.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $cashflow = Cashflow::findOrFail($id);
        $category = $cashflow->category;

        // Rollback saldo ketika transaksi dihapus
        if ($category) {
            $debitAccount = $category->debitAccount;
            $creditAccount = $category->creditAccount;

            if ($cashflow->transaction_type === 'in') {
                if ($debitAccount && $debitAccount->position === 'asset') {
                    $debitAccount->current_balance -= $cashflow->amount;
                    $debitAccount->save();
                }
            } elseif ($cashflow->transaction_type === 'out') {
                if ($debitAccount && $debitAccount->position === 'asset') {
                    $debitAccount->current_balance += $cashflow->amount;
                    $debitAccount->save();
                }
            }
        }

        $cashflow->delete();

        return redirect()->route('cashflows.index')->with('success', 'Transaksi berhasil dihapus.');
    }
   
    public function profitLossReport()
    {
        // Ambil kategori untuk laba rugi dan beban biaya
        $profitLossCategory = Category::where('code', 202)->first(); // Penjualan
        $expenseCategory = Category::where('code', 201)->first(); // Pembelian Barang Dagang
    
        // Cek apakah kategori laba rugi dan beban biaya ditemukan
        if (!$profitLossCategory || !$expenseCategory) {
            return redirect()->back()->withErrors('Kategori tidak ditemukan.');
        }
    
        // Ambil total untuk pendapatan
        $totalIncome = Cashflow::where('category_code', $profitLossCategory->code)->sum('amount');
    
        // Ambil total untuk beban biaya
        $totalExpense = Cashflow::where('category_code', $expenseCategory->code)->sum('amount');
    
        // Ambil akun terkait
        $incomeAccount = $profitLossCategory->creditAccount; // Akun untuk kategori Pendapatan
        $expenseAccount = $expenseCategory->debitAccount; // Akun untuk kategori Beban Biaya
    
        // Pastikan akun pendapatan dan beban biaya tidak null
        if (!$incomeAccount || !$expenseAccount) {
            return redirect()->back()->withErrors('Akun tidak ditemukan.');
        }
    
        // Siapkan data untuk laporan
        $profitLossAccounts = [
            [
                'code' => $incomeAccount->code,
                'name' => $incomeAccount->name,
                'balance' => $totalIncome,
            ]
        ];
    
        $expenseAccounts = [
            [
                'code' => $expenseAccount->code,
                'name' => $expenseAccount->name,
                'balance' => $totalExpense,
            ]
        ];
    
        // Hitung laba rugi bersih
        $netProfitLoss = $totalIncome - $totalExpense;
    
        // Kirim data ke view
        return view('cashflows.profit_loss_report', compact('profitLossAccounts', 'expenseAccounts', 'totalIncome', 'totalExpense', 'netProfitLoss'));
    }
    
    
}
