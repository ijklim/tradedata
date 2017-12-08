<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{
    protected $fillable = [
        'domain_name', 'api_base_url'
    ];

    use \App\Traits\Model;
}
