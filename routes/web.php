
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Agent\DashboardController;



Route::get('/', [TicketController::class, 'create'])
    ->name('tickets.create');

Route::post('/tickets', [TicketController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('tickets.store');

Route::get('/tickets/{ticket}/success', [TicketController::class, 'success'])
    ->name('tickets.success');

// Agent authentication
Route::get('/login', [LoginController::class, 'show'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('login.submit');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


Route::get('/agent/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('agent.dashboard');


Route::get('/agent/tickets/{ticket}', [DashboardController::class, 'show'])
    ->middleware('auth')
    ->name('agent.tickets.show');



Route::patch('/agent/tickets/{ticket}', [DashboardController::class, 'update'])
    ->middleware('auth')
    ->name('agent.tickets.update');


Route::post('/agent/tickets/{ticket}/generate-reply',
    [DashboardController::class, 'generateReply'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('agent.tickets.generateReply');

Route::post('/agent/tickets/{ticket}/save-reply',
    [DashboardController::class, 'saveReply'])
    ->middleware('auth')
    ->name('agent.tickets.saveReply');

