<?php

namespace App\Imports;

use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Transaction;
use App\Models\LedgerEntry;
use App\Models\Account;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class ProdukImport implements ToModel, WithHeadingRow
{
    private $rowNumber = 1; // Melacak nomor baris

    public function model(array $row)
    {
        // Periksa jika seluruh baris kosong
        if (array_filter($row, fn($value) => !is_null($value) && $value !== '') === []) {
            Log::info('Baris kosong dilewati', ['baris' => $this->rowNumber]);
            $this->rowNumber++;
            return null;
        }

        // Pastikan kolom kategori tidak kosong
        if (empty($row['kategori'])) {
            Log::warning('Baris dilewati karena kategori kosong', [
                'baris' => $this->rowNumber,
                'data' => $row
            ]);
            $this->rowNumber++;
            return null;
        }

        // Validasi dan buat kategori jika belum ada
        $kategori = Kategori::firstOrCreate(
            ['nama_kategori' => $row['kategori']],
            ['created_at' => now(), 'updated_at' => now()]
        );

        // Validasi stok
        $stok = isset($row['stok']) && is_numeric($row['stok']) ? (int) $row['stok'] : 0;

        // Generate kode_produk
        $lastProduct = Produk::orderBy('kode_produk', 'desc')->first();
        $nextId = $lastProduct ? (int) substr($lastProduct->kode_produk, 1) + 1 : 1;
        $kode_produk = 'P' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

        // Cek apakah produk sudah ada
        $existingProduct = Produk::where('nama_produk', $row['nama_produk'])->first();

        // Parsing harga beli dengan validasi
        $harga_beli = isset($row['harga_beli'])
            ? (float) str_replace(',', '', str_replace('.', '', $row['harga_beli']))
            : 0.0;

        // Validasi tambahan harga beli
        if ($harga_beli > 1000000) { // Batas harga beli tidak masuk akal
            Log::warning('Harga beli tidak masuk akal, diset menjadi 0', [
                'baris' => $this->rowNumber,
                'harga_beli' => $harga_beli,
            ]);
            $harga_beli = 0.0;
        }

        // Parsing harga jual dengan validasi
        $harga_jual = isset($row['harga_jual'])
            ? (float) preg_replace('/[^0-9]/', '', str_replace(',', '.', $row['harga_jual']))
            : 0.0;

        // Parsing diskon
        $diskon = isset($row['diskon']) && is_numeric($row['diskon']) ? (float) $row['diskon'] : 0.0;

        // Hitung total nilai untuk stok
        $totalValue = $stok * $harga_beli;

        // Update produk yang sudah ada atau buat produk baru
        if ($existingProduct) {
            $existingProduct->update([
                'stok' => $existingProduct->stok + $stok,
                'harga_beli' => $harga_beli,
                'harga_jual' => $harga_jual,
                'diskon' => $diskon,
                'id_kategori' => $kategori->id_kategori,
                'rak' => $row['rak'] ?? null,
            ]);

            // Proses transaksi untuk produk yang diperbarui
            $this->createTransaction($existingProduct, $stok, $harga_beli, $totalValue);
            $this->rowNumber++;
            return null;
        }

        // Buat produk baru jika belum ada
        $newProduct = new Produk([
            'kode_produk' => $kode_produk,
            'nama_produk' => $row['nama_produk'],
            'id_kategori' => $kategori->id_kategori,
            'merk' => $row['merk'] ?? null,
            'harga_beli' => $harga_beli,
            'harga_jual' => $harga_jual,
            'diskon' => $diskon,
            'stok' => $stok,
            'rak' => $row['rak'] ?? null,
        ]);

        // Proses transaksi untuk produk baru
        $this->createTransaction($newProduct, $stok, $harga_beli, $totalValue);
        $this->rowNumber++;
        return $newProduct;
    }


    private function createTransaction($product, $stok, $harga_beli, $totalValue)
    {
        // Buat transaksi dan entri buku besar
        $transaction = Transaction::storeStockTransaction(
            Transaction::CATEGORY_MODAL_ADD,
            $stok,
            $harga_beli,
            'Penambahan modal untuk produk: ' . $product->nama_produk
        );

        // Entri buku besar
        $debetAccount = Account::where('code', '103')->first();
        $creditAccount = Account::where('code', '201')->first();

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
    }
}
