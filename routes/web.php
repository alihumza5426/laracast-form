<?php

use Illuminate\Support\Facades\Route;
use Khan\Forms\Http\Controllers\FormTestController;
use Khan\Forms\Http\Middleware\EnsureFormTestingEnvironment;

$prefix = config('forms.routes.prefix', 'forms');
$middleware = (array) config('forms.routes.middleware', ['web']);

Route::prefix($prefix)
    ->middleware(array_merge($middleware, [EnsureFormTestingEnvironment::class]))
    ->name('forms.')
    ->group(function () {
        Route::get('/test', [FormTestController::class, 'index'])->name('test.index');
        Route::post('/test/search', [FormTestController::class, 'search'])->name('test.search');
        Route::get('/test/search', [FormTestController::class, 'search'])->name('test.search.get');
        Route::post('/test/execute', [FormTestController::class, 'search'])->name('test.execute');
        Route::get('/test/students', [FormTestController::class, 'students'])->name('test.students');
        Route::get('/test/entry/{id}', [FormTestController::class, 'show'])->name('test.entry');
        Route::match(['post', 'put', 'patch'], '/test/update/{id}', [FormTestController::class, 'update'])->name('test.update');
        Route::delete('/test/delete/{id}', [FormTestController::class, 'destroy'])->name('test.delete');
        Route::post('/test/delete/{id}', [FormTestController::class, 'destroy'])->name('test.delete.post');
        Route::post('/test/delete-bulk', [FormTestController::class, 'destroyBulk'])->name('test.delete.bulk');
    });
