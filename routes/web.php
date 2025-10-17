<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\AthleteController;
use App\Http\Controllers\HeatController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\EventController;

// 登录路由（不需要认证）
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// 所有其他路由都需要认证
Route::middleware('auth')->group(function () {
    Route::get('/', [CompetitionController::class, 'index'])->name('home');

    Route::resource('competitions', CompetitionController::class);
    Route::post('competitions/{competition}/generate-schedule-book', [CompetitionController::class, 'generateScheduleBook'])
        ->name('competitions.generate-schedule-book');

    Route::resource('events', EventController::class);

    Route::prefix('competitions/{competition}')->name('competitions.')->group(function () {
        // Grades
        Route::resource('grades', GradeController::class)->except(['show']);

        // Athletes
        Route::prefix('athletes')->name('athletes.')->group(function () {
            Route::get('/', [AthleteController::class, 'index'])->name('index');
            Route::get('/create', [AthleteController::class, 'create'])->name('create');
            Route::post('/', [AthleteController::class, 'store'])->name('store');
            Route::get('/{athlete}/edit', [AthleteController::class, 'edit'])->name('edit');
            Route::put('/{athlete}', [AthleteController::class, 'update'])->name('update');
            Route::delete('/{athlete}', [AthleteController::class, 'destroy'])->name('destroy');
            Route::post('/remove-all', [AthleteController::class, 'removeAll'])->name('remove-all');
            Route::post('/import', [AthleteController::class, 'import'])->name('import');
            Route::get('/download-template', [AthleteController::class, 'downloadTemplate'])->name('download-template');
        });

        // Heats
        Route::prefix('heats')->name('heats.')->group(function () {
            Route::get('/', [HeatController::class, 'index'])->name('index');
            Route::get('/{heat}', [HeatController::class, 'show'])->name('show');
            Route::get('/{heat}/edit', [HeatController::class, 'edit'])->name('edit');
            Route::put('/{heat}', [HeatController::class, 'update'])->name('update');
            Route::delete('/{heat}', [HeatController::class, 'destroy'])->name('destroy');
            Route::post('/generate-all', [HeatController::class, 'generateAll'])->name('generate-all');
            Route::post('/generate-field-events', [HeatController::class, 'generateFieldEvents'])->name('generate-field-events');
        });

        // Schedules
        Route::prefix('schedules')->name('schedules.')->group(function () {
            Route::get('/', [ScheduleController::class, 'index'])->name('index');
            Route::get('/field', [ScheduleController::class, 'indexField'])->name('index-field');
            Route::get('/create', [ScheduleController::class, 'create'])->name('create');
            Route::post('/', [ScheduleController::class, 'store'])->name('store');
            Route::get('/{schedule}/edit', [ScheduleController::class, 'edit'])->name('edit');
            Route::put('/{schedule}', [ScheduleController::class, 'update'])->name('update');
            Route::delete('/{schedule}', [ScheduleController::class, 'destroy'])->name('destroy');
            Route::get('/bulk-new', [ScheduleController::class, 'bulkNew'])->name('bulk-new');
            Route::post('/bulk-create', [ScheduleController::class, 'bulkCreate'])->name('bulk-create');
        });
    });
    // 结束认证保护的路由组
});
