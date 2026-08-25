<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes - Estadísticas 1.7
|--------------------------------------------------------------------------
*/

// Dashboard Routes
Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/visits', [DashboardController::class, 'visits'])->name('visits');
Route::get('/informes/dashboard-epi', [DashboardController::class, 'visits'])->name('dashboard-epi');
Route::get('/charts', [DashboardController::class, 'charts'])->name('charts');
Route::get('/informes/dashboard2', [DashboardController::class, 'charts'])->name('dashboard2');

// Report & Output Routes
Route::get('/tables', [ReportController::class, 'tables'])->name('tables');
Route::get('/registros', [ReportController::class, 'tables'])->name('registros');
Route::get('/informes', [DashboardController::class, 'charts'])->name('informes');

// Data Entry Routes
Route::get('/forms', [DataController::class, 'forms'])->name('forms');
Route::get('/ingresos', [DataController::class, 'forms'])->name('ingresos');

// Admin & UI Routes
Route::get('/typography', [AdminController::class, 'typography'])->name('typography');
Route::get('/customization', [AdminController::class, 'typography'])->name('customization');
Route::get('/ui-elements', [AdminController::class, 'uiElements'])->name('ui-elements');
Route::get('/components', [AdminController::class, 'uiElements'])->name('components');
