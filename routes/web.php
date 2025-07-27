<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskTypeController;
use App\Http\Controllers\UserController;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

require __DIR__ . '/auth.php';

// Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
//     Route::get('', [RoutingController::class, 'index'])->name('root');
// Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
// Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
//     Route::get('{any}', [RoutingController::class, 'root'])->name('any');
// });

// Route::get('any', [RoutingController::class, 'root'])->name('any');
// Route::get('first/second', [RoutingController::class, 'secondLevel'])->name('second');
// Route::get('first/second/third', [RoutingController::class, 'thirdLevel'])->name('third');

Route::get('/', [DashboardController::class, 'index'])->name('home');

Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('permissions', function () {
        $user = auth()->user();
        // dd($user->getAllPermissions()->pluck('name'));
        // dump(auth()->user()->getRoleNames());
        // dump(auth()->user()->getAllPermissions()->pluck('name'));
        // dump(auth()->user()->can('viewAny', Role::class));

    })->name('dashboard');

    Route::resource('roles', RoleController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');
    Route::resource('task-types', TaskTypeController::class)->except('show');
    Route::resource('customers', CustomerController::class);
    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    
    // Task Calendar Routes
    Route::get('tasks-calendar', [TaskController::class, 'calendar'])->name('tasks.calendar');
    Route::get('api/tasks-calendar', [TaskController::class, 'calendarData'])->name('api.tasks.calendar');
    Route::patch('api/tasks/{task}/update-date', [TaskController::class, 'updateTaskDate'])->name('api.tasks.update-date');

    // Task API CRUD routes
    Route::post('api/tasks', [TaskController::class, 'apiStore'])->name('api.tasks.store');
    Route::put('api/tasks/{task}', [TaskController::class, 'apiUpdate'])->name('api.tasks.update');
    Route::delete('api/tasks/{task}', [TaskController::class, 'apiDestroy'])->name('api.tasks.destroy');
    Route::get('api/staff-workload', [TaskController::class, 'getStaffWorkload'])->name('api.staff.workload');

    Route::get('change-password', [UserController::class,'changePasswordPage'])->name('change-password');
    Route::post('change-password', [UserController::class,'changePassword'])->name('password-change');



});

// Demo Route
Route::get('profile', function () {
    return 'Hi';
})->name('profile');

Route::get('messages', function () {
    return 'Hi';
})->name('messages');

Route::get('pricing', function () {
    return 'Hi';
})->name('pricing');

Route::get('help', function () {
    return 'Hi';
})->name('help');

Route::get('lock-screen', function () {
    return 'Hi';
})->name('lock-screen');

Route::get('demo-route', function () {
    return 'Hi';
})->name('demo-route');