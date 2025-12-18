<?php

use Illuminate\Support\Facades\Route;
use DarkCoder\Ofa\Http\Controllers\Admin\ThemeController;

Route::group(['prefix' => 'admin/ofa', 'middleware' => ['web', 'auth', 'ofa.admin']], function () {
    // Admin page
    Route::get('/', [ThemeController::class, 'page'])->name('ofa.admin.index');

    // API endpoints used by the admin app
    Route::get('themes', [ThemeController::class, 'index']);
    Route::post('themes', [ThemeController::class, 'store']);
    Route::patch('themes/{palette}', [ThemeController::class, 'update']);
    Route::delete('themes/{palette}', [ThemeController::class, 'destroy']);
    Route::post('themes/{palette}/default', [ThemeController::class, 'setDefault']);
    Route::post('themes/{palette}/preview', [ThemeController::class, 'preview']);
});

// Public route for frontend to clear preview (admin only as well)
Route::post('admin/ofa/preview/clear', [ThemeController::class, 'clearPreview'])->middleware(['web', 'auth', 'ofa.admin']);
