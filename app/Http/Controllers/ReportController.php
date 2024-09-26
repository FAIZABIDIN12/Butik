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
        $period = $request->query('period', Carbon::now()->format('m-Y'));

        if (!preg_match('/^\d{2}-\d{4}$/', $period)) {
            abort(400, 'Invalid period format. Use mm-yyyy.');
        }

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
            // Ambil saldo untuk setiap akun berdasarkan period
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

        // Debugging: Tampilkan monthlyBalances
        // dd($monthlyBalances); // Hapus atau komentari baris ini setelah debugging

        return view('report.profit_loss', compact('periods', 'period', 'incomeAccounts', 'outcomeAccounts', 'totalIncome', 'totalOutcome'));
    }
}
