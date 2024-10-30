<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika berbeda dengan konvensi
    protected $table = 'payments';

    // Tentukan kolom yang dapat diisi
    protected $fillable = [
        'penjualan_id',
        'amount',
        'metode_pembayaran',
    ];

    // Relasi ke model Penjualan
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id', 'id_penjualan');
    }
}
