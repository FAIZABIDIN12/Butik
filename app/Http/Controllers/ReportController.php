<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\MonthlyBalance; // Import MonthlyBalance
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function profitLoss(Request $request)
    {
        // Ambil parameter bulan dan tahun dari request atau gunakan nilai default
        $month = $request->get('month', Carbon::now()->format('m'));
        $year = $request->get('year', Carbon::now()->format('Y'));
        $startDate = Carbon::createFromFormat('Y-m', $year . '-' . $month)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $year . '-' . $month)->endOfMonth();
        
        // Ambil akun pendapatan dan biaya
        $incomeAccounts = Account::where('position', 'revenue')->get();
        $outcomeAccounts = Account::where('position', 'expense')->get();
    
        // Inisialisasi total pendapatan dan biaya
        $totalIncome = 0;
        $totalOutcome = 0;
    
        // Ambil saldo bulanan untuk akun pendapatan
        $incomeAccounts = $incomeAccounts->map(function ($account) use ($month, $year) {
            $monthlyBalance = MonthlyBalance::where('account_code', $account->code)
                ->where('month', "$year-$month")
                ->first();
            $balance = $monthlyBalance ? $monthlyBalance->balance : 0;
            return [
                'account' => $account,
                'balance' => $balance,
            ];
        });
    
        // Ambil saldo bulanan untuk akun biaya
        $outcomeAccounts = $outcomeAccounts->map(function ($account) use ($month, $year) {
            $monthlyBalance = MonthlyBalance::where('account_code', $account->code)
                ->where('month', "$year-$month")
                ->first();
            $balance = $monthlyBalance ? $monthlyBalance->balance : 0;
            return [
                'account' => $account,
                'balance' => $balance,
            ];
        });
    
        // Hitung total pendapatan dan biaya
        $totalIncome = $incomeAccounts->sum('balance');
        $totalOutcome = $outcomeAccounts->sum('balance');
    
        // Hitung laba rugi
        $profitLoss = $totalIncome - $totalOutcome;
    
        // Kirim data ke view
        return view('report.profit_loss', [
            'incomeAccounts' => $incomeAccounts,
            'outcomeAccounts' => $outcomeAccounts,
            'totalIncome' => $totalIncome,
            'totalOutcome' => $totalOutcome,
            'profitLoss' => $profitLoss, // Kirim laba rugi ke view jika perlu
            'month' => $month,
            'year' => $year
        ]);
    }
    
  
    public function balanceSheet(Request $request)
{
    // Ambil bulan dan tahun dari request atau gunakan nilai default
    $month = $request->get('month', Carbon::now()->format('m'));
    $year = $request->get('year', Carbon::now()->format('Y'));
    
    // Format bulan untuk querying (misalnya: '10')
    $formattedMonth = str_pad($month, 2, '0', STR_PAD_LEFT);
    
    // Ambil akun aktiva (asset) dan pasiva (liability)
    $activaAccounts = Account::where('position', 'asset')->get();
    $passivaAccounts = Account::where('position', 'liability')->get();

    // Ambil saldo bulanan untuk akun aktiva
    $activaAccounts = $activaAccounts->map(function ($account) use ($formattedMonth, $year) {
        $monthlyBalance = MonthlyBalance::where('account_code', $account->code)
            ->where('month', "$year-$formattedMonth") // Perbaiki format bulan
            ->first();
        $balance = $monthlyBalance ? $monthlyBalance->balance : 0;
        return [
            'account' => $account,
            'balance' => $balance,
        ];
    });

    // Ambil saldo bulanan untuk akun pasiva
    $passivaAccounts = $passivaAccounts->map(function ($account) use ($formattedMonth, $year) {
        $monthlyBalance = MonthlyBalance::where('account_code', $account->code)
            ->where('month', "$year-$formattedMonth") // Perbaiki format bulan
            ->first();
        $balance = $monthlyBalance ? $monthlyBalance->balance : 0;
        return [
            'account' => $account,
            'balance' => $balance,
        ];
    });

    // Hitung total aktiva dan pasiva
    $totalActiva = $activaAccounts->sum('balance');
    $totalPassiva = $passivaAccounts->sum('balance');

    // Kirim data ke view
    return view('report.balance_sheet', [
        'activaAccounts' => $activaAccounts,
        'passivaAccounts' => $passivaAccounts,
        'totalActiva' => $totalActiva,
        'totalPassiva' => $totalPassiva,
        'month' => $month,
        'year' => $year
    ]);
}

    
}
