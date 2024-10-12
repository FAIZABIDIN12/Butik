<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class Transaction extends Model
{
    const CATEGORY_STOCK_ADD = '022'; // Kode kategori untuk menambah stok
    const CATEGORY_STOCK_REDUCE = '023'; // Kode kategori untuk mengurangi stok
    const CATEGORY_MODAL_ADD = '003';

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

    public function type()
    {
        return $this->hasOne(Category::class, 'code', 'category_code');
    }

    public static function calculateSaldo()
    {
        // Menghitung saldo keseluruhan dari semua transaksi
        return self::sum('nominal');
    }

    // Tambah stok dan buat transaksi
    public static function storeStockTransaction($categoryCode, $jumlah, $harga_per_unit, $description)
    {
        // Hitung total nominal berdasarkan jumlah stok dan harga per unit
        $nominal = $jumlah * $harga_per_unit;

        return self::create([
            'transaction_at' => now(),
            'description' => $description,
            'category_code' => $categoryCode,
            'nominal' => $nominal,
            'user_id' => auth()->id(),
        ]);
    }
    public static function updateMonthlyBalance(Account $account, $amount, $type)
{
    $currentMonth = Carbon::now()->format('Y-m');

    $monthlyBalance = MonthlyBalance::firstOrNew(
        ['account_code' => $account->code, 'month' => $currentMonth],
        ['balance' => 0]
    );

    switch ($account->position) {
        case 'asset':
        case 'expense':
            if ($type === 'debit') {
                $account->current_balance += $amount;
                $monthlyBalance->balance += $amount;
            } else {
                $account->current_balance -= $amount;
                $monthlyBalance->balance -= $amount;
            }
            break;

        case 'liability':
        case 'revenue':
            if ($type === 'debit') {
                $account->current_balance -= $amount;
                $monthlyBalance->balance -= $amount;
            } else {
                $account->current_balance += $amount;
                $monthlyBalance->balance += $amount;
            }
            break;
    }

    $account->save();
    $monthlyBalance->save();
}

}
