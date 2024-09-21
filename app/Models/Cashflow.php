<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashflow extends Model
{
    use HasFactory;

    protected $table = 'cashflows';

    protected $fillable = [
        'date',
        'description',
        'transaction_type',
        'amount',
        'current_balance',
        'category_code', // Kode kategori
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_code', 'code'); // Relasi ke kategori
    }
}
