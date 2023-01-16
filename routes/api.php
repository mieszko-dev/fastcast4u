<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterStep1Controller;
use App\Http\Controllers\RegisterStep2Controller;
use App\Http\Controllers\RegisterStep3Controller;
use App\Http\Controllers\RegisterStep4Controller;
use Illuminate\Support\Facades\Route;

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


Route::post('/register/step1', RegisterStep1Controller::class)->name('step1')->middleware(['guest']);
Route::post('/register/step2', RegisterStep2Controller::class)->name('step2');
Route::post('/register/step3', RegisterStep3Controller::class)->name('step3');
Route::post('/register/step4', RegisterStep4Controller::class)->name('step4');

Route::group([
    'prefix' => 'auth'
], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);

});
