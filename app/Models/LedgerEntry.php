<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    use HasFactory;

    protected $table = 'ledger_entries';
    protected $fillable = [
        'transaction_id',
        'account_code',
        'entry_date',
        'entry_type',
        'amount',
        'balance'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_code', 'code');
    }
}
