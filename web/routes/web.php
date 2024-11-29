<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CameraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'login']);

Route::group(['middleware' => ['role:super-admin|admin|editor']], function () {
    Route::resource('roles', RoleController::class);
    Route::get('roles/{roleId}/delete', [RoleController::class, 'destroy']);

    Route::resource('users', UserController::class);
    Route::get('users/{userId}/delete', [UserController::class, 'destroy']);

    Route::get('configurations/areas', [AreaController::class, 'index'])->name('configurations.areas');
    Route::get('configurations/areas/create', [AreaController::class, 'create'])->name('configurations.areas.create');
    Route::post('configurations/areas/store', [AreaController::class, 'store'])->name('configurations.areas.store');
    Route::get('configurations/areas/{areaId}/edit', [AreaController::class, 'edit'])->name('configurations.areas.edit');
    Route::put('configurations/areas/{areaId}', [AreaController::class, 'update'])->name('configurations.areas.update');
    Route::get('configurations/areas/{areaId}/delete', [AreaController::class, 'destroy']);

    Route::get('configurations/positions', [PositionController::class, 'index'])->name('configurations.positions');
    Route::get('configurations/positions/create', [PositionController::class, 'create'])->name('configurations.positions.create');
    Route::post('configurations/positions/store', [PositionController::class, 'store'])->name('configurations.positions.store');
    Route::get('configurations/positions/{positionId}/edit', [PositionController::class, 'edit'])->name('configurations.positions.edit');
    Route::put('configurations/positions/{positionId}', [PositionController::class, 'update'])->name('configurations.positions.update');
    Route::get('configurations/positions/{areaId}/delete', [PositionController::class, 'destroy']);

    Route::get('configurations/groups', [GroupController::class, 'index'])->name('configurations.groups');
    Route::get('configurations/groups/create', [GroupController::class, 'create'])->name('configurations.groups.create');
    Route::post('configurations/groups/store', [GroupController::class, 'store'])->name('configurations.groups.store');
    Route::get('configurations/groups/{areaId}/edit', [GroupController::class, 'edit'])->name('configurations.groups.edit');
    Route::put('configurations/groups/{areaId}', [GroupController::class, 'update'])->name('configurations.groups.update');
    Route::get('configurations/groups/{areaId}/delete', [GroupController::class, 'destroy']);

    Route::get('cameras', [CameraController::class, 'index'])->name('cameras');
    Route::get('cameras/create', [CameraController::class, 'create'])->name('cameras.create');
    Route::post('cameras/store', [CameraController::class, 'store'])->name('cameras.store');
    Route::get('cameras/{cameraId}/edit', [CameraController::class, 'edit'])->name('cameras.edit');
    Route::put('cameras/{cameraId}', [CameraController::class, 'update'])->name('cameras.update');
    Route::get('cameras/{cameraId}/delete', [CameraController::class, 'destroy']);
    Route::get('cameras/detail', [CameraController::class, 'detail'])->name('cameras.detail');
    Route::get('cameras/{id}', [CameraController::class, 'getByid']);

    Route::get('events', [EventController::class, 'index'])->name('events');
    Route::get('events/{eventId}', [EventController::class, 'getById'])->name('events.get.one');
    Route::get('events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('events/store', [EventController::class, 'store'])->name('events.store');
    Route::get('events/{eventId}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('events/{eventId}', [EventController::class, 'update'])->name('events.update');
    Route::get('events/{eventId}/delete', [EventController::class, 'destroy']);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stream/{cameraId}', [DashboardController::class, 'stream']);
    Route::get('/dashboard/cameras', [DashboardController::class, 'getCameras']);
    Route::post('/dashboard/save-preferences', [DashboardController::class, 'savePreferences'])
        ->name('dashboard.save-preferences');

    Route::get('/user/profile', function () {
        return view('profile.show');
    })->name('profile.show');

    Route::get('/user/profile/authentication', function () {
        return view('profile.authentication');
    })->name('profile.authentication');

    Route::get('/user/profile/update-password', function () {
        return view('profile.update-password');
    })->name('profile.update-password');
});