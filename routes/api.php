<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\AdminController;
use App\Http\Controllers\API\V1\LoanController;
use App\Http\Controllers\API\V1\CustomerController;

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

//Api healthcheck
Route::get('/healthcheck',function(){ return ["status"=>"ok"]; });


//Defining admin api routes
Route::group(['prefix' => '/v1/admin'], function () {

    //Route to register admin user
    Route::post('/register', [AdminController::class, 'register']);

    //Route to login admin user
    Route::post('/login', [AdminController::class, 'login']);   

    //Defining authenticated admin api routes
    Route::group(['middleware' => ['auth:sanctum']], function () {

        //Route to logout admin user
        Route::get('/logout', [AdminController::class, 'logout']);

        //Routes for loan operations by admin
        Route::post('/loan/approve/{id}', [LoanController::class, 'approve']);
        Route::post('/loan/decline/{id}', [LoanController::class, 'decline']);
        Route::get('/loan/list/{status?}', [LoanController::class, 'list']);
    
    });
    
});

//Defining customer api routes
Route::group(['prefix' => '/v1/customer'], function () {

    //Route to register customer
    Route::post('/register', [CustomerController::class, 'register']);

    //Route to login customer
    Route::post('/login', [CustomerController::class, 'login']);   
    

    //Defining authenticated customer routes
    Route::group(['middleware' => ['auth:sanctum']], function () {

        //Route to logout admin user
        Route::get('/logout', [CustomerController::class, 'logout']);

        //Routes for loan operations by customer
        Route::post('/loan/apply', [LoanController::class, 'apply']);
        Route::get('/loan/view/{id?}', [LoanController::class, 'view']);
        Route::post('/loan/pay/{id}', [LoanController::class, 'pay']);
    
    });
    
});

