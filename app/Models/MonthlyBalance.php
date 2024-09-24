<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_code',
        'month',
        'balance',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_code', 'code');
    }
}
