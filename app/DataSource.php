<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{
    protected $fillable = [
        'domain_name', 'api_url'
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
            case 'domain_name':
                return strtolower($fieldValue);
                break;
            default:
                return $fieldValue;
                break;
        }
    }
}
