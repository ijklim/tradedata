<?php

use Illuminate\Database\Seeder;

class StocksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('stocks')->insert([
            'symbol' => 'QQQ',
            'name' => 'PowerShares QQQ Trust (ETF)',
        ]);

        DB::table('stocks')->insert([
            'symbol' => 'SPY',
            'name' => 'SPDR S&P 500 ETF Trust',
        ]);
    }
}
