<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/models', 'App\Http\Controllers\ModelController@getAll');
Route::get('/cameras', 'App\Http\Controllers\CameraController@getAll');
Route::get('/{id}/rtsp-url', 'App\Http\Controllers\CameraController@getRtspUrl');
Route::post('/alarms', 'App\Http\Controllers\EventController@store');
Route::put('/alarms/update', 'App\Http\Controllers\EventController@update');
Route::get('/alarms/{id}', 'App\Http\Controllers\CameraController@getById');