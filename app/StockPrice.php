<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockPrice extends Model
{
    protected $fillable = [
        'symbol', 'date', 'open', 'high', 'low', 'close', 'volume'
    ];

    use \App\Traits\Model;

    /**
     * Format field value if necessary based on field name.
     *
     * @param  string  $fieldName
     * @param  mixed  $fieldValue
     * @return mixed
     */
    public static function formatField($fieldName, $fieldValue) {
        switch ($fieldName) {
            case 'symbol':
                return strtoupper($fieldValue);
                break;
            default:
                return $fieldValue;
                break;
        }
    }
    
    public function stock()
    {
        return $this->belongsTo(\App\Stock::class, 'symbol');
    }
}
