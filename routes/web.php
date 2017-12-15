<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', 'StockController@index');

Route::resource('stock', StockController::class);
Route::resource('data-source', DataSourceController::class);

// Api that returns json
Route::get('/stock-price/{stock}', 'StockPriceController@show');