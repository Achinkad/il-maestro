<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\NamespaceController;



/* --- [API Routes] -> Users --- */
Route::resource('users', UserController::class);
Route::get('user', [UserController::class, 'showUserLoggedIn'])->middleware('auth:api');

/* --- [API Routes] -> Auth --- */
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

/* --- [API Routes] -> Nodes --- */
Route::get('nodes', [NodeController::class, 'getAllNodes']);
Route::get('nodes/master', [NodeController::class, 'getMasterNodes']);
Route::post('nodes/create', [NodeController::class, 'registerMasterNode']);

/* --- [API Routes] -> Namespaces --- */
Route::get('namespaces', [NamespaceController::class, 'getNamespaces']);



