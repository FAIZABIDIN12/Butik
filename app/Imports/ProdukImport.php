<?php

namespace App\Imports;

use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Transaction;
use App\Models\LedgerEntry;
use App\Models\Account; 
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProdukImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Cek apakah kategori sudah ada berdasarkan nama
        $kategori = Kategori::firstOrCreate(
            ['nama_kategori' => $row['kategori']],
            ['created_at' => now(), 'updated_at' => now()]
        );
    
       // Validasi stok
$stok = isset($row['stok']) && is_numeric($row['stok']) ? (int) $row['stok'] : 0;

// Ambil kode produk terakhir dari database, urutkan berdasarkan kode_produk
$lastProduct = Produk::orderBy('kode_produk', 'desc')->first();
$nextId = $lastProduct ? (int) substr($lastProduct->kode_produk, 1) + 1 : 1;
$kode_produk = 'P' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

// Cek apakah produk sudah ada di database berdasarkan nama_produk
$existingProduct = Produk::where('nama_produk', $row['nama_produk'])->first();

// Validasi harga_beli
$harga_beli = isset($row['harga_beli']) && !empty($row['harga_beli']) 
    ? floatval(str_replace('.', '', $row['harga_beli'])) // Menghapus titik jika ada
    : 0.0;

// Validasi harga_jual
$harga_jual = isset($row['harga_jual']) && !empty($row['harga_jual']) 
    ? floatval(str_replace('.', '', $row['harga_jual'])) // Menghapus titik jika ada
    : 0.0;

// Validasi diskon
$diskon = isset($row['diskon']) && is_numeric($row['diskon']) 
    ? floatval($row['diskon']) // Mengonversi diskon menjadi float
    : 0.0;

// Ambil nilai rak dari row data
$rak = isset($row['rak']) ? $row['rak'] : null;

// Hitung total nilai produk
$totalValue = $stok * $harga_beli;
    
        // Jika produk sudah ada, update data produk
        if ($existingProduct) {
            $existingProduct->update([
                'stok' => $stok,
                'harga_beli' => $harga_beli,
                'harga_jual' => $harga_jual,
                'diskon' => $diskon,
                'id_kategori' => $kategori->id_kategori,
                'rak' => $rak,
            ]);
    
            // Buat transaksi untuk penambahan modal
            $transaction = Transaction::storeStockTransaction(
                Transaction::CATEGORY_MODAL_ADD,
                $stok, // Amount
                $harga_beli, // Price per unit
                'Penambahan modal untuk produk: ' . $row['nama_produk']
            );
    
            // Update monthly balance dan buat ledger entry
            $debetAccount = Account::where('code', '103')->first(); // Modal account
            $creditAccount = Account::where('code', '201')->first(); // HPP account
    
            // Update monthly balance dan ledger entry untuk debit
            if ($debetAccount) {
                Transaction::updateMonthlyBalance($debetAccount, $totalValue, 'debit');
                LedgerEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_code' => $debetAccount->code,
                    'entry_date' => now(),
                    'entry_type' => 'debit',
                    'amount' => $totalValue,
                    'balance' => $debetAccount->current_balance,
                ]);
            }
    
            // Update monthly balance dan ledger entry untuk kredit
            if ($creditAccount) {
                Transaction::updateMonthlyBalance($creditAccount, $totalValue, 'credit');
                LedgerEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_code' => $creditAccount->code,
                    'entry_date' => now(),
                    'entry_type' => 'credit',
                    'amount' => $totalValue,
                    'balance' => $creditAccount->current_balance,
                ]);
            }
    
            return null; // Tidak perlu mengembalikan model baru jika produk sudah ada
        }
    
        // Jika produk belum ada, buat model produk baru
        $newProduct = new Produk([
            'kode_produk' => $kode_produk,
            'nama_produk' => $row['nama_produk'],
            'id_kategori' => $kategori->id_kategori,
            'merk' => array_key_exists('merk', $row) ? $row['merk'] : null,
            'harga_beli' => $harga_beli,
            'harga_jual' => $harga_jual,
            'diskon' => $diskon,
            'stok' => $stok,
            'rak' => $rak,
        ]);
    
        // Buat transaksi untuk penambahan modal
        $transaction = Transaction::storeStockTransaction(
            Transaction::CATEGORY_MODAL_ADD,
            $stok, // Amount
            $harga_beli, // Price per unit
            'Penambahan modal untuk produk: ' . $row['nama_produk']
        );
    
        // Update monthly balance dan ledger entry
        $debetAccount = Account::where('code', '103')->first(); // Modal account
        $creditAccount = Account::where('code', '201')->first(); // HPP account
    
        // Update monthly balance dan ledger entry untuk debit
        if ($debetAccount) {
            Transaction::updateMonthlyBalance($debetAccount, $totalValue, 'debit');
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => $debetAccount->code,
                'entry_date' => now(),
                'entry_type' => 'debit',
                'amount' => $totalValue,
                'balance' => $debetAccount->current_balance,
            ]);
        }
    
        // Update monthly balance dan ledger entry untuk kredit
        if ($creditAccount) {
            Transaction::updateMonthlyBalance($creditAccount, $totalValue, 'credit');
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => $creditAccount->code,
                'entry_date' => now(),
                'entry_type' => 'credit',
                'amount' => $totalValue,
                'balance' => $creditAccount->current_balance,
            ]);
        }
    
        return $newProduct; // Return the new product model
    }
    
    
}
