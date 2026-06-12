<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\ClashController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/search', [SearchController::class, 'searchLive']);
Route::get('/character/{api_id}', [CharacterController::class, 'show']);
Route::post('/character/{api_id}/chat', [CharacterController::class, 'chat']);

Route::get('/clash', [ClashController::class, 'setup']);
Route::get('/clash/{id1}/{id2}', [ClashController::class, 'show']);
Route::post('/clash/{id1}/{id2}/chat', [ClashController::class, 'chat']);

Route::get('/install-db', function() {
    Artisan::call('migrate', ['--force' => true]);
    return Artisan::output();
});
