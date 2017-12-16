<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'symbol', 'name', 'data_source_id'
    ];

    // Required when primary key is not $id and not an incrementing number
    protected $primaryKey = 'symbol';
    public $incrementing = false;
    protected $keyType = 'string';

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

    public function dataSource()
    {
        return $this->belongsTo(DataSource::class, 'data_source_id');
    }

    public function stockPrices()
    {
        return $this->hasMany(StockPrice::class, 'symbol');
    }
}
