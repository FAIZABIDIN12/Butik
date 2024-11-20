<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'initial_balance', 'current_balance', 'position'];

    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    public function debetCategories()
    {
        return $this->hasMany(Category::class, 'debit', 'code');
    }

    public function creditCategories()
    {
        return $this->hasMany(Category::class, 'credit', 'code');
    }

    public function monthlyBalances()
    {
        return $this->hasMany(MonthlyBalance::class, 'account_code', 'code');
    }

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class, 'account_code', 'code');
    }

    public function scopeLabaRugi($query)
    {
        return $query->where('position', 'Laba Rugi');
    }
}
