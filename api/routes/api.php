<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\K3SController;
use App\Http\Controllers\NamespaceController;

/* --- [API Routes] -> Users --- */
Route::resource('users', UserController::class);
Route::get('user', [UserController::class, 'showUserLoggedIn'])->middleware('auth:api');

/* --- [API Routes] -> Auth --- */
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

/* --- [API Routes] -> Nodes --- */
Route::post('nodes', [K3SController::class, 'getNodes']);

/* --- [API Routes] -> Namespaces --- */
Route::post('namespaces', [NamespaceController::class, 'showNamespaces']);

