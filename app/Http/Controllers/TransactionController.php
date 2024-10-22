<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\Account;
use App\Models\MonthlyBalance;
use App\Models\LedgerEntry;
use App\Imports\TransactionImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
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
        } elseif ($transaction->category->type === 'mutation') {
            // Misalkan kita anggap mutation sebagai kredit
            $transaction->credit = $transaction->nominal;
        }
    }

    // Update saldo
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

    // Memformat nominal transaksi ke integer
    $nominal = str_replace('.', '', $request->nominal);

    // Menyimpan transaksi baru
    $transaction = Transaction::create([
        'transaction_at' => now(),
        'description' => $request->description,
        'category_code' => $request->category_code,
        'nominal' => $nominal,
        'user_id' => Auth::id(),
    ]);

    // **Tambahan: Penanganan khusus untuk kode transaksi 003**
    if ($request->category_code === '003') {
        // Jika kategori transaksi adalah 003, tambahkan saldo untuk akun 103 dan 201
        $specialDebetAccount = Account::where('code', '103')->first();
        $specialCreditAccount = Account::where('code', '201')->first();

        // Proses akun debit 103
        if ($specialDebetAccount) {
            $this->updateMonthlyBalance($specialDebetAccount, $nominal, 'debit');
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => $specialDebetAccount->code,
                'entry_date' => now(),
                'entry_type' => 'debit',
                'amount' => $nominal,
                'balance' => $specialDebetAccount->current_balance,
            ]);
        }

        // Proses akun kredit 201
        if ($specialCreditAccount) {
            $this->updateMonthlyBalance($specialCreditAccount, $nominal, 'credit');
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => $specialCreditAccount->code,
                'entry_date' => now(),
                'entry_type' => 'credit',
                'amount' => $nominal,
                'balance' => $specialCreditAccount->current_balance,
            ]);
        }
    }

    // Proses akun debit
    if ($debetAccount) {
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

    // Tambahan: Update saldo akun laba rugi (203)
    $currentMonth = Carbon::now()->format('Y-m');
    $profitLossAccountCode = '203'; // Kode akun laba rugi

    // Cari atau buat entri saldo bulanan untuk akun laba rugi
    $profitLossMonthlyBalance = MonthlyBalance::firstOrNew(
        [
            'account_code' => $profitLossAccountCode,
            'month' => $currentMonth,
        ],
        [
            'balance' => 0, // Default balance jika belum ada
        ]
    );

    // Hitung perubahan saldo laba rugi berdasarkan posisi akun
    $profitChange = 0;
    if ($debetAccount && $debetAccount->position === 'expense') {
        $profitChange -= $nominal; // Biaya mengurangi laba
    }
    if ($creditAccount && $creditAccount->position === 'revenue') {
        $profitChange += $nominal; // Pendapatan menambah laba
    }

    // Update saldo akun laba rugi
    $profitLossMonthlyBalance->balance += $profitChange;
    $profitLossMonthlyBalance->save();

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

    public function edit($id)
    {
        $transaction = Transaction::findOrFail($id); // Fetch the transaction by ID
        return response()->json(['transaction' => $transaction]); // Return the transaction data as JSON
    }
    
    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id); // Fetch the transaction by ID
    
        // Validate the request
        $request->validate([
            'description' => 'required|string',
            'category_code' => 'required|string',
            'nominal' => 'required|numeric',
        ]);
    
        // Store the old nominal value to adjust the balances
        $oldNominal = $transaction->nominal;
    
        // Update the transaction with new data
        $transaction->description = $request->description;
        $transaction->category_code = $request->category_code;
        $transaction->nominal = $request->nominal; // Adjust according to your attribute names
        $transaction->save(); // Save the changes
    
        // Adjust the account balances for the update
        $this->adjustAccountBalances($transaction, $oldNominal, $request->nominal);
    
        return response()->json(['success' => 'Transaction updated successfully!']);
    }
    
    private function adjustAccountBalances($transaction, $oldNominal, $newNominal)
    {
        // Determine the category of the transaction
        $category = Category::where('code', $transaction->category_code)->first();
    
        // Retrieve the debit and credit accounts
        $debetAccount = Account::where('code', $category->debit_account_code)->first();
        $creditAccount = Account::where('code', $category->credit_account_code)->first();
    
        // Adjust balances for the old nominal
        $this->updateMonthlyBalance($debetAccount, $oldNominal, 'credit'); // Reverse the effect of old debit
        $this->updateMonthlyBalance($creditAccount, $oldNominal, 'debit'); // Reverse the effect of old credit
    
        // Adjust balances for the new nominal
        $this->updateMonthlyBalance($debetAccount, $newNominal, 'debit'); // Apply the new debit
        $this->updateMonthlyBalance($creditAccount, $newNominal, 'credit'); // Apply the new credit
    }
    
    
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
    
        // Get the nominal value and category code before deleting
        $nominal = $transaction->nominal;
        $category = Category::where('code', $transaction->category_code)->first();
    
        // Retrieve the debit and credit accounts
        $debetAccount = Account::where('code', $category->debit_account_code)->first();
        $creditAccount = Account::where('code', $category->credit_account_code)->first();
    
        // Adjust balances for the deleted transaction
        $this->updateMonthlyBalance($debetAccount, $nominal, 'credit'); // Reverse the effect of debit
        $this->updateMonthlyBalance($creditAccount, $nominal, 'debit'); // Reverse the effect of credit
    
        // Delete the transaction
        $transaction->delete();
    
        return redirect()->route('transaction.index')->with('success', 'Transaction deleted successfully.');
    }
    
    
    public function import(Request $request)
    {
        // Validate that the file is present and is an Excel file
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        // Try to import the file and handle any errors
        try {
            // Perform the import using the TransactionImport class
            Excel::import(new TransactionImport, $request->file('file'));

            // If successful, return a success message
            return redirect()->back()->with('success', 'Transactions imported successfully!');
        } catch (\Exception $e) {
            // If there is any error, log the error and return an error message
            \Log::error('Transaction Import Error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'There was an issue importing the transactions. Please try again.');
        }
    }
}
