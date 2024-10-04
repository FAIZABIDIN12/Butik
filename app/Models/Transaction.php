<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_at',
        'description',
        'category_code',
        'nominal',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class,  'account_code', 'code');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_code', 'code');
    }

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }
     // Determine if the transaction is a debit or credit
     public function type()
     {
         return $this->hasOne(Category::class, 'code', 'category_code');
     }
     public static function calculateSaldo()
     {
         // Assuming 'nominal' is positive for incoming and negative for outgoing
         return self::sum('nominal');
     }
     public function produk()
     {
         return $this->belongsTo(Produk::class, 'id_produk');
     }
}
