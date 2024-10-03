<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\Account;
use App\Models\MonthlyBalance;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        // Ambil parameter tanggal dari request
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Format tanggal jika ada
        $startDateFormatted = $startDate ? Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay() : null;
        $endDateFormatted = $endDate ? Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay() : null;

        // Ambil transaksi dengan filter tanggal jika ada
        $transactions = Transaction::with('category')
            ->when($startDateFormatted, function ($query) use ($startDateFormatted) {
                return $query->where('transaction_at', '>=', $startDateFormatted);
            })
            ->when($endDateFormatted, function ($query) use ($endDateFormatted) {
                return $query->where('transaction_at', '<=', $endDateFormatted);
            })
            ->get();

        // Inisialisasi saldo
        $saldo = 0;

        // Hitung saldo untuk setiap transaksi
        foreach ($transactions as $transaction) {
            // Inisialisasi debit dan kredit
            $transaction->debit = 0;
            $transaction->credit = 0;

            // Hitung saldo cumulatif
            if ($transaction->category) {
                if ($transaction->category->type === 'in') {
                    $transaction->debit = $transaction->nominal;
                } elseif ($transaction->category->type === 'out') {
                    $transaction->credit = $transaction->nominal;
                }
            }
            
            $saldo += $transaction->debit - $transaction->credit;
            $transaction->saldo = $saldo;
        }

        // Ambil semua akun dan kategori
        $accounts = Account::all();
        $categories = Category::all();

        return view('transaction.index', compact('transactions', 'accounts', 'categories', 'startDateFormatted', 'endDateFormatted', 'saldo'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'description' => 'required|string|max:255',
            'category_code' => 'required|exists:categories,code',
            'nominal' => 'required|numeric',
        ]);
    
        // Mengambil kategori transaksi
        $category = Category::where('code', $request->category_code)->first();
    
        if (!$category) {
            return redirect()->back()->withErrors(['category_code' => 'Invalid category code.']);
        }
    
        // Mengambil akun debit dan kredit berdasarkan kategori transaksi
        $debetAccount = Account::where('code', $category->debit_account_code)->first();
        $creditAccount = Account::where('code', $category->credit_account_code)->first();
    
        // Memformat nominal transaksi ke integer untuk memastikan input tidak bermasalah
        $nominal = str_replace('.', '', $request->nominal);
    
        // Menyimpan transaksi baru
        $transaction = Transaction::create([
            'transaction_at' => now(),
            'description' => $request->description,
            'category_code' => $request->category_code,
            'nominal' => $nominal,
            'user_id' => Auth::id(),
        ]);
    
        // Proses akun debit
        if ($debetAccount) {
            // Cek jika posisi akun adalah asset atau expense
            $this->updateMonthlyBalance($debetAccount, $nominal, 'debit');
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => $debetAccount->code,
                'entry_date' => now(),
                'entry_type' => 'debit',
                'amount' => $nominal,
                'balance' => $debetAccount->current_balance,
            ]);
        }
    
        // Proses akun kredit
        if ($creditAccount) {
            // Cek jika posisi akun adalah liability atau revenue
            $this->updateMonthlyBalance($creditAccount, $nominal, 'credit');
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => $creditAccount->code,
                'entry_date' => now(),
                'entry_type' => 'credit',
                'amount' => $nominal,
                'balance' => $creditAccount->current_balance,
            ]);
        }
    
        return redirect()->route('transaction.index')->with('success', 'Transaction added successfully.');
    }
    

    private function updateMonthlyBalance(Account $account, $amount, $type)
    {
        // Ambil bulan dan tahun saat ini
        $currentMonth = Carbon::now()->format('Y-m');
        
        // Cari atau buat monthly balance untuk akun ini pada bulan berjalan
        $monthlyBalance = MonthlyBalance::firstOrNew(
            [
                'account_code' => $account->code,
                'month' => $currentMonth,
            ],
            [
                'balance' => 0, // Default balance jika belum ada
            ]
        );

        // Update current balance berdasarkan posisi akun dan tipe transaksi (debit/kredit)
        switch ($account->position) {
            case 'asset': // Aktiva
            case 'expense': // Biaya
                if ($type === 'debit') {
                    $account->current_balance += $amount; // Menambah saldo
                    $monthlyBalance->balance += $amount; // Menambah saldo bulanan
                } else {
                    $account->current_balance -= $amount; // Mengurangi saldo
                    $monthlyBalance->balance -= $amount; // Mengurangi saldo bulanan
                }
                break;

            case 'liability': // Liabilitas
            case 'revenue': // Pendapatan
                if ($type === 'debit') {
                    $account->current_balance -= $amount; // Mengurangi saldo
                    $monthlyBalance->balance -= $amount; // Mengurangi saldo bulanan
                } else {
                    $account->current_balance += $amount; // Menambah saldo
                    $monthlyBalance->balance += $amount; // Menambah saldo bulanan
                }
                break;
        }

        // Simpan perubahan saldo pada akun dan saldo bulanan
        $account->save();
        $monthlyBalance->save();
    }

  
}
