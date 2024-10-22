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
    const CATEGORY_INCOME = '001';  // Kode kategori untuk Penjualan Barang (Income)
const CATEGORY_MUTATION = '012'; // Kode kategori untuk Mutasi Kas Butik Ke FO

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
        if (!$account) {
            throw new \Exception('Akun tidak ditemukan');
        }
    
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
    
    public static function createSalesTransaction($amount, $description)
    {
        // Buat transaksi penjualan barang
        $transaction = self::create([
            'transaction_at' => now(),
            'description' => $description,
            'category_code' => self::CATEGORY_INCOME,
            'nominal' => $amount,
            'user_id' => auth()->id(),
        ]);
    
        // Periksa apakah akun Kas Butik ada
        $kasButik = Account::where('name', 'Kas Butik')->first();
        if (!$kasButik) {
            throw new \Exception('Akun Kas Butik tidak ditemukan');
        }
    
        // Periksa apakah akun Pendapatan Butik ada
        $pendapatanButik = Account::where('name', 'Pendapatan Butik')->first();
        if (!$pendapatanButik) {
            throw new \Exception('Akun Pendapatan Butik tidak ditemukan');
        }
    
        // Perbarui saldo kas butik (debit)
        self::updateMonthlyBalance($kasButik, $amount, 'debit');
    
        // Perbarui saldo pendapatan butik (kredit)
        self::updateMonthlyBalance($pendapatanButik, $amount, 'credit');
    
        return $transaction;
    }
    
    public static function createMutationTransaction($amount, $description)
    {
        // Buat transaksi mutasi kas butik ke FO
        $transaction = self::create([
            'transaction_at' => now(),
            'description' => $description,
            'category_code' => self::CATEGORY_MUTATION,
            'nominal' => $amount,
            'user_id' => auth()->id(),
        ]);
    
        // Periksa apakah akun Kas Butik dan Kas FO ada
        $kasButik = Account::where('name', 'Kas Butik')->first();
        if (!$kasButik) {
            throw new \Exception('Akun Kas Butik tidak ditemukan');
        }
    
        $kasFO = Account::where('name', 'Kas FO')->first();
        if (!$kasFO) {
            throw new \Exception('Akun Kas FO tidak ditemukan');
        }
    
        // Perbarui saldo kas butik (kredit)
        self::updateMonthlyBalance($kasButik, $amount, 'credit');
    
        // Perbarui saldo kas FO (debit)
        self::updateMonthlyBalance($kasFO, $amount, 'debit');
    
        return $transaction;
    }
    

}
