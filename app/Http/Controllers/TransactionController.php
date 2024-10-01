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
    
        // Format tanggal jika ada, gunakan format Y-m-d agar cocok dengan format date di database
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
    
        // Hitung debit, kredit, dan saldo untuk setiap transaksi
        foreach ($transactions as $transaction) {
            // Inisialisasi debit dan kredit
            $transaction->debit = 0;
            $transaction->credit = 0;
    
            // Tentukan nilai debit dan kredit berdasarkan tipe kategori
            if ($transaction->category) {
                if ($transaction->category->type === 'in') {
                    $transaction->debit = $transaction->nominal;
                } elseif ($transaction->category->type === 'out') {
                    $transaction->credit = $transaction->nominal;
                }
            }
    
            // Hitung saldo cumulatif
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

    $category = Category::where('code', $request->category_code)->first();

    if (!$category) {
        return redirect()->back()->withErrors(['category_code' => 'Invalid category code.']);
    }

    // Mengambil akun debit dan kredit berdasarkan kategori
    $debetAccount = Account::where('code', $category->debit_account_code)->first(); 
    $creditAccount = Account::where('code', $category->credit_account_code)->first(); 

    // Memformat nominal
    $nominal = str_replace('.', '', $request->nominal);

    // Menyimpan transaksi
    $transaction = Transaction::create([
        'transaction_at' => now(),
        'description' => $request->description,
        'category_code' => $request->category_code,
        'nominal' => $nominal,
        'user_id' => Auth::id(),
    ]);

    // Update saldo untuk akun debit (Kas Butik)
    if ($debetAccount) {
        $this->updateMonthlyBalance($debetAccount, $nominal, 'debit'); // Kas Butik bertambah
        // Entri jurnal untuk akun debit
        LedgerEntry::create([
            'transaction_id' => $transaction->id,
            'account_code' => $debetAccount->code,
            'entry_date' => now(),
            'entry_type' => 'debit',
            'amount' => $nominal,
            'balance' => $debetAccount->current_balance + $nominal, // Saldo bertambah
        ]);
    }

    // Update saldo untuk akun kredit (Pendapatan HPP BD)
    if ($creditAccount) {
        $this->updateMonthlyBalance($creditAccount, $nominal, 'credit'); // Pendapatan HPP BD bertambah
        // Entri jurnal untuk akun kredit
        LedgerEntry::create([
            'transaction_id' => $transaction->id,
            'account_code' => $creditAccount->code,
            'entry_date' => now(),
            'entry_type' => 'credit',
            'amount' => $nominal,
            'balance' => $creditAccount->current_balance + $nominal, // Saldo bertambah
        ]);
    }

    return redirect()->route('transaction.index')->with('success', 'Transaction added successfully.');
}

private function updateMonthlyBalance(?Account $account, $nominal, $type)
{
    if ($account) {
        $currentMonth = Carbon::now()->format('m-Y'); // Format bulan dan tahun saat ini

        // Mencari saldo bulanan untuk akun ini dan bulan ini
        $monthlyBalance = MonthlyBalance::where('account_code', $account->code)
            ->where('month', $currentMonth)
            ->first();

        // Update current balance sesuai dengan tipe transaksi
        switch ($account->position) {
            case 'asset':  
                if ($type === 'debit') {
                    $account->current_balance += $nominal; // Bertambah untuk debit
                } elseif ($type === 'credit') {
                    $account->current_balance -= $nominal; // Berkurang untuk kredit
                }
                break;

            case 'liability':
                if ($type === 'debit') {
                    $account->current_balance -= $nominal; // Berkurang untuk debit
                } elseif ($type === 'credit') {
                    $account->current_balance += $nominal; // Bertambah untuk kredit
                }
                break;

            case 'revenue':
                if ($type === 'debit') {
                    $account->current_balance -= $nominal; // Berkurang untuk debit
                } elseif ($type === 'credit') {
                    $account->current_balance += $nominal; // Bertambah untuk kredit
                }
                break;

            case 'expense':
                if ($type === 'debit') {
                    $account->current_balance += $nominal; // Bertambah untuk debit
                } elseif ($type === 'credit') {
                    $account->current_balance -= $nominal; // Berkurang untuk kredit
                }
                break;
        }

        // Simpan perubahan saldo akun
        $account->save();

        // Update saldo bulanan atau buat catatan baru jika belum ada
        if ($monthlyBalance) {
            // Jika sudah ada catatan untuk bulan ini, tambahkan nominalnya
            if ($type === 'debit') {
                $monthlyBalance->balance += $nominal;
            } elseif ($type === 'credit') {
                $monthlyBalance->balance -= $nominal;
            }
        } else {
            // Jika belum ada, buat entri baru untuk saldo bulanan
            $monthlyBalance = new MonthlyBalance();
            $monthlyBalance->account_code = $account->code;
            $monthlyBalance->month = $currentMonth;
            $monthlyBalance->balance = $account->current_balance; 
        }

        // Simpan saldo bulanan
        $monthlyBalance->save();
    }
}

    }
