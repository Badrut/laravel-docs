<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;     
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkerRoleController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\KopSuratController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/export/{id}', [ProjectController::class, 'exportWord'])->name('projects.export.word');
    Route::get('/projects/export-absensi/{id}', [ProjectController::class, 'exportAbsensi'])->name('projects.export.absensi');
    Route::get('/projects/export-kwitansi/{id}', [ProjectController::class, 'exportKwitansi'])->name('projects.export.kwitansi');
    Route::get('/projects/createv2', [ProjectController::class, 'createv2'])->name('projects.create-v2');
    Route::get('/projects/{project}/editv2', [ProjectController::class, 'editv2'])->name('projects.edit-v2');
    Route::post('/projects/import-rab', [ProjectController::class, 'importRab'])->name('projects.import-rab');
    Route::resource('users', UserController::class);
    Route::resource('roles', WorkerRoleController::class);
    Route::resource('workers', WorkerController::class);  
    Route::post('/workers/import', [WorkerController::class, 'importExcel'])->name('workers.import');
    Route::post('/workers/store-role-ajax', [WorkerController::class, 'storeRoleAjax'])->name('workers.store-role-ajax');
    Route::resource('contractors', ContractorController::class);
    Route::resource('kop-surat', KopSuratController::class);
    Route::resource('projects', ProjectController::class)->names('projects');  
    
});