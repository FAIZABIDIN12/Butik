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
                'code' => '100',
                'name' => 'Kas Butik',
                'position' => 'asset',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'code' => '101',
                'name' => 'Modal',
                'position' => 'liability',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'code' => '102',
                'name' => 'HPP Barang Dagang',
                'position' => 'asset',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'code' => '103',
                'name' => 'Pendapatan HPP BD',
                'position' => 'revenue',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'code' => '104',
                'name' => 'Laba Berjalan',
                'position' => 'liability',
                'initial_balance' => 0,
                'current_balance' => 0,
            ],
        ]);
    }
}
