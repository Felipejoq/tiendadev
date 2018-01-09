<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
 * Buyer routes
 */
Route::resource('buyers', 'Buyer\BuyerController',['only' => ['index','show']]);

/*
 * Categories routes
 */
Route::resource('categories', 'Category\CategoryController',['except' => ['create','edit']]);

/*
 * Products routes
 */
Route::resource('products', 'Product\ProductController',['only' => ['index','show']]);

/*
 * Transactions routes
 */
Route::resource('transactions', 'Transaction\TransactionController',['only' => ['index','show']]);

/*
 * Sellers routes
 */
Route::resource('sellers', 'Seller\SellerController',['only' => ['index','show']]);

/*
 * Users routes
 */
Route::resource('users', 'User\UserController', ['except' => ['create','edit']]);

