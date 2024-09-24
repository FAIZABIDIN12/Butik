<?php 

namespace App\Http\Controllers;

use App\Models\LedgerEntry;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function profitLossReport()
{
    // Fetch total income for account code 103 (Pendapatan HPP BD)
    $totalIncome = LedgerEntry::where('account_code', 103)
        ->where('entry_type', 'credit')
        ->sum('amount');

    // Fetch total HPP for account code 102 (HPP Barang Dagang)
    $totalHPP = LedgerEntry::where('account_code', 102)
        ->where('entry_type', 'debit')
        ->sum('amount');

    // Calculate profit/loss
    $profitLoss = $totalIncome - $totalHPP;

    // Return the view with the calculated data
    return view('report.profitloss', [
        'total_income' => number_format($totalIncome, 2),
        'total_hpp' => number_format($totalHPP, 2),
        'profit_loss' => number_format($profitLoss, 2),
    ]);
}

    
}
