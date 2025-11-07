<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientSaleController;
use App\Http\Controllers\ClientPurchaseController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Yuvirion CRM - API Routes (JSON Only)
|--------------------------------------------------------------------------
| For Postman, Vue, React, Flutter
| Sanctum + Spatie Permission
|--------------------------------------------------------------------------
*/

// AUTH API
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'apiLogin']);
    Route::post('logout', [AuthController::class, 'apiLogout'])->middleware('auth:sanctum');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

// SUPER ADMIN
Route::middleware(['auth:sanctum', 'role:Super Admin'])->group(function () {
    Route::apiResource('modules', ModuleController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('client-sales', ClientSaleController::class);
    Route::apiResource('client-purchases', ClientPurchaseController::class);
    Route::apiResource('leads', LeadController::class);
    Route::apiResource('deals', DealController::class);
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('tickets', TicketController::class);
});

// CLIENTS (Permission Based)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('clients', [ClientController::class, 'index'])->middleware('permission:view_clients');
    Route::post('clients', [ClientController::class, 'store'])->middleware('permission:create_clients');
    Route::get('clients/{client}', [ClientController::class, 'show'])->middleware('permission:view_clients');
    Route::put('clients/{client}', [ClientController::class, 'update'])->middleware('permission:edit_clients');
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])->middleware('permission:delete_clients');
});

// SALES & PURCHASES
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('sales', ClientSaleController::class);
    Route::apiResource('purchases', ClientPurchaseController::class);
    Route::get('invoices/{invoice}', [ClientSaleController::class, 'downloadInvoice']);
});

// LEADS & DEALS
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('leads', LeadController::class);
    Route::apiResource('deals', DealController::class);
    Route::post('deals/{deal}/sale', [SaleController::class, 'store']);
    Route::post('sales/{sale}/reverse', [SaleController::class, 'reverse']);
});

// TASKS
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::post('tasks/{task}/complete', [TaskController::class, 'complete']);
});

// TICKETS
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('tickets', TicketController::class);
    Route::get('my-tickets', [TicketController::class, 'myTickets'])->middleware('role:Client');
});

// CLIENT PORTAL API
Route::middleware(['auth:sanctum', 'role:Client'])->prefix('client')->group(function () {
    Route::get('dashboard', [ClientController::class, 'dashboard']);
    Route::get('sales', [ClientSaleController::class, 'mySales']);
    Route::get('purchases', [ClientPurchaseController::class, 'myPurchases']);
    Route::get('tickets', [TicketController::class, 'myTickets']);
});

// REPORTS
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('reports/clients', [ReportController::class, 'clientReports']);
    Route::get('reports/sales', [ReportController::class, 'salesReports']);
    Route::get('reports/employees', [ReportController::class, 'employeeReports']);
    Route::get('reports/overview', [ReportController::class, 'overview']);
});