<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{
    use HasFactory;

    protected $table = 'withdrawals'; // Specify the table name if it's not pluralized

    protected $fillable = [
        'payment_id', // Foreign key to the payments table
        'amount',     // Amount withdrawn
        'method',     // Withdrawal method ('tunai' or 'non_tunai')
    ];

    /**
     * Define the relationship with the Payment model.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class); // Adjust if the Payment model is in a different namespace
    }
}
