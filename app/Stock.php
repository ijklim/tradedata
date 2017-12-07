<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'symbol', 'name'
    ];

    // Required when primary key is not $id and not an incrementing number
    protected $primaryKey = 'symbol';
    public $incrementing = false;
    protected $keyType = 'string';


    public function stockPrices()
    {
        return $this->hasMany(StockPrice::class, 'symbol');
    }
}
