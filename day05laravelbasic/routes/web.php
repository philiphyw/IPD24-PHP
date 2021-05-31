<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
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
// Laravel 8
Route::get('/products', [ProductsController::class, 'index']);

// // Laravel 8 spring syntax
Route::get('/products/springsyntax','App\Http\Controllers\ProductsCOntroller@springsyntaxindex');

//before Laravel
// Route::get('/products', 'ProductsController@index');


Route::get('/about', [ProductsController::class,'about']);

Route::get('/productarray',[ProductsController::class,'productArray']);