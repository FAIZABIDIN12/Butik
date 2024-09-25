<?php 

namespace App\Http\Controllers;

use App\Models\LedgerEntry;
use App\Models\Account;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function profitLossReport(Request $request)
    {
        // Get the selected month from the request, or default to the current month
        $selectedMonth = $request->input('month') ?: date('Y-m');

        // Fetch total income for account code 103 (Pendapatan HPP BD)
        $totalIncome = LedgerEntry::where('account_code', 103)
            ->where('entry_type', 'credit')
            ->whereMonth('entry_date', date('m', strtotime($selectedMonth))) // Filter by month
            ->whereYear('entry_date', date('Y', strtotime($selectedMonth))) // Filter by year
            ->sum('amount');

        // Fetch total HPP for account code 102 (HPP Barang Dagang)
        $totalHPP = LedgerEntry::where('account_code', 102)
            ->where('entry_type', 'debit')
            ->whereMonth('entry_date', date('m', strtotime($selectedMonth))) // Filter by month
            ->whereYear('entry_date', date('Y', strtotime($selectedMonth))) // Filter by year
            ->sum('amount');

        // Calculate profit/loss
        $profitLoss = $totalIncome - $totalHPP;

        // If there is a net profit, update the current balance for the Laba Rugi account
        if ($profitLoss > 0) {
            // Fetch the Laba Rugi account (using code 103 as an example)
            $labaRugiAccount = Account::where('code', '104')->first(); // Ensure this is the correct code for Laba Rugi

            if ($labaRugiAccount) {
                // Update the current balance
                $labaRugiAccount->current_balance += $profitLoss;
                $labaRugiAccount->save(); // Save the updated account
            }
        }

        // Return the view with the calculated data
        return view('report.profitloss', [
            'total_income' => number_format($totalIncome, 2),
            'total_hpp' => number_format($totalHPP, 2),
            'profit_loss' => number_format($profitLoss, 2),
        ]);
    }
}
