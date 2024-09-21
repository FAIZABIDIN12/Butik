<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $primaryKey = 'code'; 
    public $incrementing = false; 
    protected $keyType = 'string'; 

    protected $fillable = [
        'code',
        'type',
        'name',
        'debit_account_code',
        'credit_account_code',
        'note',
    ];

    public $timestamps = true;

    // Definisikan relasi dengan model Account
    public function debitAccount()
    {
        return $this->belongsTo(Account::class, 'debit_account_code', 'code');
    }

    public function creditAccount()
    {
        return $this->belongsTo(Account::class, 'credit_account_code', 'code');
    }
}
