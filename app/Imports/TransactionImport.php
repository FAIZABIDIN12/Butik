<?php

namespace App\Imports;

use App\Models\Transaction;
use App\Models\LedgerEntry;
use App\Models\Account; 
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class TransactionImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        \Log::info('Row data: ', $row);

        if (empty($row['kode']) || empty($row['tanggal'])) {
            \Log::warning('Data tidak lengkap, melewati baris: ', $row);
            return null; // Melewati baris ini
        }
        // Retrieve or create necessary accounts
        $kasButik = Account::firstOrCreate(
            ['code' => '102'], 
            ['name' => 'Kas Butik', 'created_at' => now(), 'updated_at' => now()]
        );
        $pendapatanButik = Account::firstOrCreate(
            ['code' => '401'], 
            ['name' => 'Pendapatan Butik', 'created_at' => now(), 'updated_at' => now()]
        );
        $kasFO = Account::firstOrCreate(
            ['code' => '101'], 
            ['name' => 'Kas FO', 'created_at' => now(), 'updated_at' => now()]
        );
        $labaBerjalan = Account::firstOrCreate(
            ['code' => '203'], 
            ['name' => 'Laba Berjalan', 'created_at' => now(), 'updated_at' => now()]
        );

        // Parse the transaction amount
        $transactionDate = $this->parseDate($row['tanggal']);
        $description = $row['keterangan'] ?? 'No description';

        // Handle pemasukan (income)
        if (!empty($row['pemasukan']) && $this->parseAmount($row['pemasukan']) > 0) {
            $amount = $this->parseAmount($row['pemasukan']);
            $transaction = $this->createOrUpdateTransaction($description, $amount, 'income', $transactionDate);
            $this->updateLedgerAndBalance($kasButik, $pendapatanButik, $amount, $transaction, 'income', $labaBerjalan);
        }

        // Handle pengeluaran (expense)
        if (!empty($row['pengeluaran']) && $this->parseAmount($row['pengeluaran']) > 0) {
            $amount = $this->parseAmount($row['pengeluaran']);
            $transaction = $this->createOrUpdateTransaction($description, $amount, 'mutation', $transactionDate);
            $this->updateLedgerAndBalance($kasFO, $kasButik, $amount, $transaction, 'mutation', $labaBerjalan);
        }
    }

    private function createOrUpdateTransaction($description, $amount, $transactionType, $transactionDate)
    {
        // Set the category code based on the transaction type
        $categoryCode = $transactionType === 'income' ? Transaction::CATEGORY_INCOME : Transaction::CATEGORY_MUTATION;

        // Create a new transaction
        return Transaction::create([
            'description' => $description,
            'nominal' => $amount,
            'transaction_type' => $transactionType,
            'transaction_at' => $transactionDate,
            'category_code' => $categoryCode,
            'user_id' => auth()->id(),
        ]);
    }

    private function updateLedgerAndBalance($debetAccount, $creditAccount, $amount, $transaction, $transactionType, $labaBerjalan)
    {
        // Update debit account balance and create ledger entry
        if ($debetAccount) {
            Transaction::updateMonthlyBalance($debetAccount, $amount, 'debit');
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => $debetAccount->code,
                'entry_date' => now(),
                'entry_type' => 'debit',
                'amount' => $amount,
                'balance' => $debetAccount->current_balance,
            ]);
        }

        // Update credit account balance and create ledger entry
        if ($creditAccount) {
            Transaction::updateMonthlyBalance($creditAccount, $amount, 'credit');
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => $creditAccount->code,
                'entry_date' => now(),
                'entry_type' => 'credit',
                'amount' => $amount,
                'balance' => $creditAccount->current_balance,
            ]);
        }

        // Update Laba Berjalan account balance based on transaction type
        if ($transactionType === 'income') {
            Transaction::updateMonthlyBalance($labaBerjalan, $amount, 'credit');
        } else if ($transactionType === 'expense') {
            Transaction::updateMonthlyBalance($labaBerjalan, $amount, 'debit');
        }
    }

    private function parseAmount($amount)
    {
        if (empty($amount)) {
            return 0;
        }

        $cleanAmount = preg_replace('/[^\d,]/', '', $amount);
        if (empty($cleanAmount)) {
            return 0;
        }

        if (strpos($cleanAmount, ',') !== false) {
            $cleanAmount = str_replace(',', '.', $cleanAmount);
        }

        $cleanAmount = str_replace('.', '', $cleanAmount);
        return (int) $cleanAmount;
    }

    private function parseDate($date)
    {
        if (is_numeric($date)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date));
        }

        return Carbon::createFromFormat('d/m/Y', $date);
    }
}
