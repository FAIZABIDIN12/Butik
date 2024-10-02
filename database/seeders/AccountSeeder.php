<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('accounts')->insert([
            [
                'code' => '101',
                'name' => 'Kas FO',
                'position' => 'asset',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'code' => '102',
                'name' => 'Kas Butik',
                'position' => 'asset',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'code' => '103',
                'name' => 'HPP Barang Dagang',
                'position' => 'asset',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'code' => '104',
                'name' => 'Inventaris Komputer',
                'position' => 'asset',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'code' => '201',
                'name' => 'Modal',
                'position' => 'liability',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'code' => '202',
                'name' => 'Laba Ditahan',
                'position' => 'liability',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'code' => '203',
                'name' => 'Laba Berjalan',
                'position' => 'liability',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'code' => '401',
                'name' => 'Pendapatan Butik',
                'position' => 'revenue',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'code' => '501',
                'name' => 'Biaya HPP',
                'position' => 'expense',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'code' => '502',
                'name' => 'Biaya Gaji',
                'position' => 'expense',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'code' => '503',
                'name' => 'Biaya Operasional',
                'position' => 'expense',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
        ]);
    }
}
