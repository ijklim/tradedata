<?php

use Faker\Generator as Faker;

const MAX = 1000;
const MIN = 500;
$factory->define(App\StockPrice::class, function (Faker $faker) {
    return [
        'symbol' => 'QQQ',
        'date' => $faker->date('Y-m-d'),
        'open' => $faker->randomFloat(2, MIN, MAX),
        'high' => $faker->randomFloat(2, (MIN + MAX) / 2, MAX),
        'low' => $faker->randomFloat(2, MIN, (MIN + MAX) / 2),
        'close' => $faker->randomFloat(2, MIN, MAX),
        'volume' => $faker->randomNumber(6)
    ];
});
