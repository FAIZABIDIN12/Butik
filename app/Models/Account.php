<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $primaryKey = 'code'; // Kode sebagai primary key
    public $incrementing = false; // Non-auto-incrementing primary key
    protected $keyType = 'string'; // Tipe primary key

    protected $fillable = [
        'code',
        'name',
        'position',
        'initial_balance',
        'current_balance',
    ];

    public $timestamps = true;
}
