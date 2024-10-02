<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\MonthlyBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function balanceSheet(Request $request)
    {
        // Default values for month and year
        $month = $request->query('month', null);
        $year = $request->query('year', null);
    
        // Validate month and year only if they are provided
        if ($month && $year) {
            if (!preg_match('/^\d{4}$/', $year) || !preg_match('/^(0[1-9]|1[0-2])$/', $month)) {
                abort(400, 'Invalid month or year selected.');
            }
    
            // Construct the period in mm-yyyy format
            $period = "{$month}-{$year}";
        } else {
            // Set the default period to the current month and year
            $period = Carbon::now()->format('m-Y');
        }
    
        // Fetch available periods and data as before
        $periods = MonthlyBalance::select('month')->distinct()->orderBy('month', 'desc')->get();
        $accounts = Account::all();
        $monthlyBalances = MonthlyBalance::where('month', $period)->get()->keyBy('account_code');
    
        // Separated accounts
        $activaAccounts = []; // Aset
        $passivaAccounts = []; // Kewajiban dan Ekuitas
        $totalActiva = 0;
        $totalPassiva = 0;
    
        foreach ($accounts as $account) {
            $balance = $monthlyBalances->get($account->code);
            $accountBalance = $balance ? $balance->balance : 0;
    
            // Separate into Aset or Kewajiban/Equitas
            if ($account->position == 'asset') {
                $activaAccounts[] = [
                    'account' => $account,
                    'balance' => $accountBalance
                ];
                $totalActiva += $accountBalance;
            } elseif ($account->position == 'liability' || $account->position == 'equity') {
                $passivaAccounts[] = [
                    'account' => $account,
                    'balance' => $accountBalance
                ];
                $totalPassiva += $accountBalance;
            }
        }
    
        // Check if assets match liabilities + equity
        $isBalanced = $totalActiva == $totalPassiva;
    
        return view('report.balance_sheet', compact('periods', 'period', 'activaAccounts', 'passivaAccounts', 'totalActiva', 'totalPassiva', 'isBalanced'));
    }
    
    public function profitLoss(Request $request)
    {
        $period = $request->query('period', Carbon::now()->format('m-Y'));
    
        if (!preg_match('/^\d{2}-\d{4}$/', $period)) {
            abort(400, 'Invalid period format. Use mm-yyyy.');
        }
    
        // Fetch available periods
        $periods = MonthlyBalance::select('month')->distinct()->orderBy('month', 'desc')->get();
        $accounts = Account::all();
        $monthlyBalances = MonthlyBalance::where('month', $period)->get()->keyBy('account_code');
    
        // Separate accounts for revenue and expenses
        $incomeAccounts = [];
        $outcomeAccounts = [];
        $totalIncome = 0;
        $totalOutcome = 0;
    
        foreach ($accounts as $account) {
            $balance = $monthlyBalances->get($account->code);
            $accountBalance = $balance ? $balance->balance : 0;
    
            // Check for revenue (income) and expense
            if ($account->position == 'revenue') {
                $incomeAccounts[] = [
                    'account' => $account,
                    'balance' => $accountBalance
                ];
                $totalIncome += $accountBalance;
            } elseif ($account->position == 'expense') {
                $outcomeAccounts[] = [
                    'account' => $account,
                    'balance' => $accountBalance
                ];
                $totalOutcome += $accountBalance;
            }
        }
    
        // Calculate profit or loss
        $profitLoss = $totalIncome - $totalOutcome;
    
        // Save profit/loss in a specific account if needed
        $profitLossAccountCode = '203'; // Replace with the actual account code for profit/loss
        
        // Get or create a monthly balance entry for the profit/loss account
        $monthlyBalanceEntry = MonthlyBalance::firstOrNew([
            'month' => $period,
            'account_code' => $profitLossAccountCode,
        ]);
    
        // Update and save the balance for profit/loss
        $monthlyBalanceEntry->balance = $profitLoss;
        $monthlyBalanceEntry->save();
    
        return view('report.profit_loss', compact('periods', 'period', 'incomeAccounts', 'outcomeAccounts', 'totalIncome', 'totalOutcome', 'profitLoss'));
    }
}
