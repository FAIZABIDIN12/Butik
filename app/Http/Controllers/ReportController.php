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
    
        $activaAccounts = [];
        $passivaAccounts = [];
        $totalActiva = 0;
        $totalPassiva = 0;
    
        foreach ($accounts as $account) {
            $balance = $monthlyBalances->get($account->code);
            $accountBalance = $balance ? $balance->balance : 0;
    
            if ($account->position == 'asset') {
                $activaAccounts[] = [
                    'account' => $account,
                    'balance' => $accountBalance
                ];
                $totalActiva += $accountBalance;
            } elseif ($account->position == 'liability') {
                $passivaAccounts[] = [
                    'account' => $account,
                    'balance' => $accountBalance
                ];
                $totalPassiva += $accountBalance;
            }
        }
    
        return view('report.balance_sheet', compact('periods', 'period', 'activaAccounts', 'passivaAccounts', 'totalActiva', 'totalPassiva'));
    }
    
    
    public function profitLoss(Request $request)
    {
        $period = $request->query('period', Carbon::now()->format('m-Y'));
    
        if (!preg_match('/^\d{2}-\d{4}$/', $period)) {
            abort(400, 'Invalid period format. Use mm-yyyy.');
        }
    
        $periods = MonthlyBalance::select('month')->distinct()->orderBy('month', 'desc')->get();
        $accounts = Account::all();
        $monthlyBalances = MonthlyBalance::where('month', $period)->get()->keyBy('account_code');
    
        $incomeAccounts = [];
        $outcomeAccounts = [];
        $totalIncome = 0;  
        $totalOutcome = 0; 
    
        foreach ($accounts as $account) {
            $balance = $monthlyBalances->get($account->code);
            $accountBalance = $balance ? $balance->balance : 0;
    
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
    
        // Calculate profit/loss
        $profitLoss = $totalIncome - $totalOutcome;
    
        // Define the account code for profit and loss (replace with your actual account code)
        $profitLossAccountCode = '104'; // Replace with the actual account code for profit/loss
    
        // Check if there is already a monthly balance entry for the profit/loss account
        $monthlyBalanceEntry = MonthlyBalance::firstOrNew([
            'month' => $period,
            'account_code' => $profitLossAccountCode,
        ]);
    
        // Update the balance for the profit/loss account
        $monthlyBalanceEntry->balance = $profitLoss;
        $monthlyBalanceEntry->save(); // Save the entry to the database
    
        return view('report.profit_loss', compact('periods', 'period', 'incomeAccounts', 'outcomeAccounts', 'totalIncome', 'totalOutcome'));
    }
    
}
