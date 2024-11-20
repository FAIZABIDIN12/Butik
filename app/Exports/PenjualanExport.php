<?php

namespace App\Exports;

use App\Models\Penjualan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PenjualanExport implements FromCollection, WithHeadings
{
    protected $penjualan;

    public function __construct($penjualan)
    {
        $this->penjualan = $penjualan;
    }

    public function collection()
    {
        $data = [];

        // Loop untuk mengambil data dari Penjualan dan PenjualanDetail
        foreach ($this->penjualan as $penjualan) {
            foreach ($penjualan->details as $detail) {
                // Hitung total harga per item (harga jual * jumlah)
                $totalHarga = $detail->harga_jual * $detail->jumlah;

                // Hitung diskon per item dalam persen, jika diskon ada
                $diskon = $detail->diskon ?? 0;

                // Hitung subtotal dengan diskon dalam persen
                $subTotal = $totalHarga - ($totalHarga * ($diskon / 100));

                $data[] = [
                    'id_penjualan' => $penjualan->id_penjualan,
                    'id_member' => $penjualan->member ? $penjualan->member->id_member : null,
                    'nama_produk' => $detail->produk->nama_produk,
                    'harga_beli' => $detail->produk->harga_beli,
                    'harga_jual' => $detail->produk->harga_jual,
                    'jumlah' => $detail->jumlah,
                    'total_harga' => $totalHarga,  // Hitung total harga (harga jual * jumlah)
                    'diskon' => $diskon,  // Pastikan diskon ada atau 0
                    'subtotal' => $subTotal,  // Subtotal setelah diskon dalam persen
                    'metode_pembayaran' => $penjualan->metode_pembayaran,
                    'nama_user' => $penjualan->user ? $penjualan->user->name : null,
                    'tanggal' => $penjualan->created_at,
                ];
            }
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'ID Penjualan',
            'ID Member',
            'Nama Produk',
            'Harga Beli',
            'Harga Jual',
            'Jumlah',
            'Total Harga',
            'Diskon Produk',
            'Sub Total',
            'Metode Pembayaran',
            'Nama User',
            'Tanggal'
        ];
    }
}
