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
        $startDateFormatted = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $endDateFormatted = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
    
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
                    $transaction->debit = $transaction->nominal; // Jika masuk, simpan nominalnya di debit
                } elseif ($transaction->category->type === 'out') {
                    $transaction->credit = $transaction->nominal; // Jika keluar, simpan nominalnya di kredit
                }
            }
    
            // Hitung saldo cumulatif
            $saldo += $transaction->debit - $transaction->credit;
            $transaction->saldo = $saldo; // Simpan saldo kumulatif
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

    // Mencari kategori berdasarkan category_code
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

    // Penanganan untuk jenis transaksi penambahan modal
    if ($category->code == '200') { // Misalkan '200' adalah kode untuk penambahan modal
        // Update saldo Kas Butik (debit)
        if ($debetAccount) {
            $debetAccount->current_balance += $nominal; // Tambah ke saldo Kas Butik
            $debetAccount->save(); // Simpan perubahan saldo
        }

        // Update saldo Modal (kredit)
        if ($creditAccount) {
            $creditAccount->current_balance += $nominal; // Tambah ke saldo Modal
            $creditAccount->save(); // Simpan perubahan saldo
        }
    } 
    // Penanganan untuk jenis transaksi penjualan
    else if ($category->code == '202') { // Misalkan '202' adalah kode untuk penjualan
        // Update saldo Kas Butik (debit)
        if ($debetAccount) {
            $debetAccount->current_balance += $nominal; // Tambah ke saldo Kas Butik
            $debetAccount->save(); // Simpan perubahan saldo
        }

        // Update saldo Pendapatan HPP BD (kredit)
        $creditAccount = Account::where('code', '103')->first(); // Misalkan '103' adalah kode untuk Pendapatan HPP BD
        if ($creditAccount) {
            $creditAccount->current_balance += $nominal; // Tambah ke saldo Pendapatan HPP BD
            $creditAccount->save(); // Simpan perubahan saldo
        }
    } 
    // Penanganan untuk jenis transaksi lainnya (misalnya pembelian)
    else {
        if ($debetAccount) {
            $debetAccount->current_balance += $nominal; // Tambah ke saldo akun debit
            $debetAccount->save(); // Simpan perubahan saldo
        }

        if ($creditAccount) {
            $creditAccount->current_balance -= $nominal; // Kurangi dari saldo akun kredit
            $creditAccount->save(); // Simpan perubahan saldo
        }
    }

    // Mencatat entri jurnal di ledger
    // Entri debit
    if ($debetAccount) {
        LedgerEntry::create([
            'transaction_id' => $transaction->id,
            'account_code' => $debetAccount->code,
            'entry_date' => now(),
            'entry_type' => 'debit',
            'amount' => $nominal,
            'balance' => $debetAccount->current_balance, // Saldo setelah transaksi
        ]);
    }

    // Entri kredit
    if ($creditAccount) {
        LedgerEntry::create([
            'transaction_id' => $transaction->id,
            'account_code' => $creditAccount->code,
            'entry_date' => now(),
            'entry_type' => 'credit',
            'amount' => $category->code == '200' ? $nominal : -$nominal, // Untuk transaksi penambahan modal, kredit sama dengan nominal, untuk yang lain negatif
            'balance' => $creditAccount->current_balance, // Saldo setelah transaksi
        ]);
    }

    return redirect()->route('transaction.index')->with('success', 'Transaction added successfully.');
}

    
    protected function updateMonthlyBalance(Account $account, string $month, float $amount)
    {
        $monthlyBalance = MonthlyBalance::where('account_code', $account->code)
            ->where('month', $month)
            ->first();
        if ($monthlyBalance) {
            $monthlyBalance->balance += $amount;
            $monthlyBalance->save();
        }
    }

    public function showLabaRugi(Request $request)
{
    // Validasi input jika diperlukan
    $request->validate([
        'month' => 'nullable|integer|min:1|max:12',
        'year' => 'nullable|integer|digits:4',
    ]);

    // Dapatkan bulan dan tahun dari permintaan, jika tidak diberikan gunakan bulan dan tahun saat ini
    $month = $request->month ?? now()->format('m');
    $year = $request->year ?? now()->format('Y');

    // Ambil transaksi untuk bulan dan tahun yang dipilih
    $transactions = Transaction::whereYear('transaction_at', $year)
        ->whereMonth('transaction_at', $month)
        ->get();

    // Hitung total pendapatan (misal: kategori penjualan)
    $totalPendapatan = $transactions->where('category_code', '202')->sum('nominal');

    // Hitung total biaya (misal: kategori HPP)
    $totalBiaya = $transactions->where('category_code', '201')->sum('nominal'); // Misal 301 adalah kode untuk HPP

    // Hitung laba rugi
    $labaRugi = $totalPendapatan - $totalBiaya;

    // Tampilkan view dengan data
    return view('transaction.labarugi', compact('transactions', 'totalPendapatan', 'totalBiaya', 'labaRugi', 'month', 'year'));
}

    
}
