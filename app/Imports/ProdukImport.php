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
        // Pastikan kolom kategori tidak kosong dan valid
        if (empty($row['kategori'])) {
            // Log atau lewati baris ini jika 'kategori' kosong
            \Log::warning('Baris dilewati karena kategori kosong', $row);
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

        // Parsing harga dan diskon
        $harga_beli = isset($row['harga_beli']) ? floatval(str_replace('.', '', $row['harga_beli'])) : 0.0;
        $harga_jual = isset($row['harga_jual']) ? floatval(str_replace('.', '', $row['harga_jual'])) : 0.0;
        $diskon = isset($row['diskon']) ? floatval($row['diskon']) : 0.0;

        // Hitung total nilai untuk stok
        $totalValue = $stok * $harga_beli;

        // Update produk yang sudah ada atau buat produk baru
        if ($existingProduct) {
            $existingProduct->update([
                'stok' => $stok,
                'harga_beli' => $harga_beli,
                'harga_jual' => $harga_jual,
                'diskon' => $diskon,
                'id_kategori' => $kategori->id_kategori,
                'rak' => $row['rak'] ?? null,
            ]);

            // Proses transaksi
            $this->createTransaction($existingProduct, $stok, $harga_beli, $totalValue);
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

        // Proses transaksi
        $this->createTransaction($newProduct, $stok, $harga_beli, $totalValue);
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
