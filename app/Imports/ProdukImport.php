<?php

namespace App\Imports;

use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Transaction;
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
    
        // Validasi harga_beli, jika kosong berikan nilai default 0
        $harga_beli = isset($row['harga_beli']) && !empty($row['harga_beli']) ? $row['harga_beli'] : 0;
    
        // Validasi harga_jual
        $harga_jual = isset($row['harga_jual']) && !empty($row['harga_jual']) ? $row['harga_jual'] : 0;
    
        // Validasi diskon
        $diskon = isset($row['diskon']) ? $row['diskon'] : 0;
    
        // Ambil nilai rak dari row data
        $rak = isset($row['rak']) ? $row['rak'] : null; // Add this line
    
        // Hitung total nilai produk yang akan dicatat dalam transaksi
        $totalValue = $stok * $harga_jual;
    
        // Jika produk sudah ada, update data dengan nilai dari Excel
        if ($existingProduct) {
            $existingProduct->update([
                'stok' => $stok,
                'harga_beli' => $harga_beli,
                'harga_jual' => $harga_jual,
                'diskon' => $diskon,
                'id_kategori' => $kategori->id_kategori,
                'rak' => $rak, // Add this line to update rak
            ]);
    
            // Create a transaction for the modal addition
            Transaction::storeStockTransaction(
                Transaction::CATEGORY_MODAL_ADD,
                $stok, // Amount
                $harga_jual, // Price per unit
                'Penambahan modal untuk produk: ' . $row['nama_produk']
            );
    
            // Update monthly balance for the modal and HPP accounts
            $debetAccount = Account::where('code', '103')->first(); // Modal account
            $creditAccount = Account::where('code', '201')->first(); // HPP account
    
            Transaction::updateMonthlyBalance($debetAccount, $totalValue, 'debit');
            Transaction::updateMonthlyBalance($creditAccount, $totalValue, 'credit');
    
            return null; // Tidak perlu mengembalikan model baru
        }
    
        // Kembalikan model baru jika produk tidak ada
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
    
        // Create a transaction for the modal addition
        Transaction::storeStockTransaction(
            Transaction::CATEGORY_MODAL_ADD,
            $stok, // Amount
            $harga_beli, // Price per unit
            'Penambahan modal untuk produk: ' . $row['nama_produk']
        );
    
        // Update monthly balance for the modal and HPP accounts
        $debetAccount = Account::where('code', '103')->first(); // Modal account
        $creditAccount = Account::where('code', '201')->first(); // HPP account
    
        Transaction::updateMonthlyBalance($debetAccount, $totalValue, 'debit');
        Transaction::updateMonthlyBalance($creditAccount, $totalValue, 'credit');
    
        return $newProduct; // Return the new product model
    }
    
}
