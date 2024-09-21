<?php

namespace App\Observers;

use App\Models\Cashflow;
use App\Models\Account;

class CashflowObserver
{
    public function creating(Cashflow $cashflow)
    {
        // Ambil kategori transaksi yang dipilih
        $category = $cashflow->category;

        // Cek apakah kategori memiliki akun debit dan kredit
        if ($category) {
            // Cek jenis transaksi (debit/kredit) dari cashflow
            if ($cashflow->transaction_type === 'debit') {
                // Update saldo di akun debit
                $debitAccount = $category->debitAccount;
                if ($debitAccount) {
                    $debitAccount->current_balance += $cashflow->amount;
                    $debitAccount->save();
                }
            } elseif ($cashflow->transaction_type === 'credit') {
                // Update saldo di akun kredit
                $creditAccount = $category->creditAccount;
                if ($creditAccount) {
                    $creditAccount->current_balance -= $cashflow->amount;
                    $creditAccount->save();
                }
            }
        }

        // Update saldo current_balance pada cashflow
        $previousBalance = Cashflow::orderBy('date', 'desc')->first()->current_balance ?? 0;
        if ($cashflow->transaction_type === 'debit') {
            $cashflow->current_balance = $previousBalance + $cashflow->amount;
        } else {
            $cashflow->current_balance = $previousBalance - $cashflow->amount;
        }
    }
}
