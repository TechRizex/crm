<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientSaleController;
use App\Http\Controllers\ClientPurchaseController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Yuvirion CRM - Web Routes (Browser Only)
|--------------------------------------------------------------------------
| Only for Admin Panel, Client Portal, Dashboard
| Uses Blade Views + Session Auth
|--------------------------------------------------------------------------
*/

// PUBLIC
Route::get('/', [AuthController::class, 'showLoginForm'])->name('home');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// AUTHENTICATED DASHBOARD
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// SUPER ADMIN PANEL
Route::middleware(['auth', 'role:Super Admin'])->prefix('admin')->group(function () {
    Route::resource('modules', ModuleController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::post('roles/{role}/permissions/update', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');
    Route::resource('users', UserController::class);
    Route::resource('clients', ClientController::class);
    Route::resource('client-sales', ClientSaleController::class);
    Route::resource('client-purchases', ClientPurchaseController::class);
    Route::resource('leads', LeadController::class);
    Route::resource('deals', DealController::class);
    Route::post('deals/{deal}/sale', [SaleController::class, 'store'])->name('deals.sale');
    Route::post('sales/{sale}/reverse', [SaleController::class, 'reverse'])->name('sales.reverse');
    Route::resource('tasks', TaskController::class);
    Route::resource('tickets', TicketController::class);
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('logs', [ReportController::class, 'logs'])->name('reports.logs');
    Route::get('settings', [DashboardController::class, 'settings'])->name('admin.settings');
    Route::post('settings/update', [DashboardController::class, 'updateSettings'])->name('admin.settings.update');
});

// MANAGER
Route::middleware(['auth', 'role:Manager|Admin'])->prefix('manager')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'managerDashboard'])->name('manager.dashboard');
    Route::get('team', [UserController::class, 'team'])->name('manager.team');
    Route::get('clients', [ClientController::class, 'index'])->name('manager.clients');
    Route::get('tasks', [TaskController::class, 'assigned'])->name('manager.tasks');
    Route::get('reports', [ReportController::class, 'managerReports'])->name('manager.reports');
});

// EMPLOYEE
Route::middleware(['auth', 'role:Employee'])->prefix('employee')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'employeeDashboard'])->name('employee.dashboard');
    Route::get('tasks', [TaskController::class, 'myTasks'])->name('employee.tasks');
    Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])->name('employee.tasks.complete');
    Route::get('leads', [LeadController::class, 'myLeads'])->name('employee.leads');
});

// CLIENT PORTAL
Route::middleware(['auth', 'role:Client'])->prefix('client')->group(function () {
    Route::get('dashboard', [ClientController::class, 'dashboard'])->name('client.dashboard');
    Route::get('profile', [ClientController::class, 'profile'])->name('client.profile');
    Route::post('profile/update', [ClientController::class, 'updateProfile'])->name('client.profile.update');
    Route::get('sales', [ClientSaleController::class, 'mySales'])->name('client.sales');
    Route::get('purchases', [ClientPurchaseController::class, 'myPurchases'])->name('client.purchases');
    Route::get('invoice/{id}', [ClientSaleController::class, 'downloadInvoice'])->name('client.invoice.download');
    Route::get('tickets', [TicketController::class, 'myTickets'])->name('client.tickets');
    Route::get('tickets/new', [TicketController::class, 'create'])->name('client.tickets.create');
    Route::post('tickets', [TicketController::class, 'store'])->name('client.tickets.store');
});