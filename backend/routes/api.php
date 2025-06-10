<?php

use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\DeveloperController;
use App\Http\Controllers\Api\SalespersonController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// Game routes
Route::apiResource('games', GameController::class);
Route::delete('games/session', [GameController::class, 'destroyBySession']);

// Developer routes
Route::apiResource('developers', DeveloperController::class);

// Salesperson routes
Route::apiResource('salespeople', SalespersonController::class);

// Project routes
Route::apiResource('projects', ProjectController::class);
Route::post('projects/{project}/assign', [ProjectController::class, 'assignDeveloper']);
