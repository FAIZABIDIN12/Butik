<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'debit_account_code', 'credit_account_code', 'note', 'type'];

    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'category_code', 'code');
    }

    public function debitAccount()
    {
        return $this->belongsTo(Account::class, 'debit_account_code', 'code');
    }

    public function creditAccount()
    {
        return $this->belongsTo(Account::class, 'credit_account_code', 'code');
    }
}
