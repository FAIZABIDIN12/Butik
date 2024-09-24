<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([
            [
                'code' => '200',
                'type' => 'in',
                'name' => 'Penambahan Modal',
                'debit_account_code' => '100', // Kas Butik
                'credit_account_code' => '101', // Modal
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '201',
                'type' => 'out',
                'name' => 'Pembelian Barang Dagang',
                'debit_account_code' => '102', // HPP Barang Dagang
                'credit_account_code' => '100', // Kas Butik
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '202',
                'type' => 'in',
                'name' => 'Penjualan',
                'debit_account_code' => '100', // Kas Butik
                'credit_account_code' => '103', // Pendapatan HPP BD
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
