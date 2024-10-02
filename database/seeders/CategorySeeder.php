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
                'code' => '001',
                'type' => 'in',
                'name' => 'Penjualan Barang',
                'debit_account_code' => '102', 
                'credit_account_code' => '401', 
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '002',
                'type' => 'in',
                'name' => 'Penambahan Modal',
                'debit_account_code' => '102', 
                'credit_account_code' => '201', 
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '011',
                'type' => 'out',
                'name' => 'Pembelian Barang Dagang',
                'debit_account_code' => '103',
                'credit_account_code' => '102', 
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '012',
                'type' => 'mutation',
                'name' => 'Mutasi Kas Butik Ke FO',
                'debit_account_code' => '101',
                'credit_account_code' => '102', 
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '013',
                'type' => 'out',
                'name' => 'Biaya Operasional',
                'debit_account_code' => '503',
                'credit_account_code' => '102', 
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '021',
                'type' => 'out',
                'name' => 'Biaya Gaji Staf Butik',
                'debit_account_code' => '502',
                'credit_account_code' => '201', 
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '022',
                'type' => 'in',
                'name' => 'Cek Stok (Bertambah)',
                'debit_account_code' => '103',
                'credit_account_code' => '401', 
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '023',
                'type' => 'out',
                'name' => 'Cek Stok (Berkurang)',
                'debit_account_code' => '501',
                'credit_account_code' => '103', 
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
