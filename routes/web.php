<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;     
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkerRoleController;
use App\Http\Controllers\WorkerController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/loogin', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/export/{id}', [ProjectController::class, 'exportWord'])->name('projects.export.word');
    Route::get('/projects/export-absensi/{id}', [ProjectController::class, 'exportAbsensi'])->name('projects.export.absensi');
    Route::get('/projects/export-kwitansi/{id}', [ProjectController::class, 'exportKwitansi'])->name('projects.export.kwitansi');
    Route::resource('users', UserController::class);
    Route::resource('roles', WorkerRoleController::class);
    Route::resource('workers', WorkerController::class);  
    Route::resource('projects', ProjectController::class)->names('projects');  
});