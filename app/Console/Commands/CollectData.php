<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CollectData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tradedata:collect {stock?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect stock price data from an external source';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Attempt to collect data for all stocks
        foreach (\App\Stock::all() as $stock) {
            $this->line('Processing ' . $stock->symbol . '...');
            $this->line(\App\Http\Controllers\StockController::collectData($stock));
        };
        
    }
}
