<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockPrice extends Model
{
    protected $fillable = [
        'symbol', 'date', 'open', 'high', 'low', 'close', 'volume'
    ];
}
