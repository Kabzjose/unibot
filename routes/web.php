<?php
use App\Http\Controllers\{AdminController, AuthController, ChatController};
use App\Http\Middleware\AdminOnly;
use Illuminate\Support\Facades\{Route, URL};

if (app()->environment('production')) URL::forceScheme('https');

Route::view('/', 'chat');
Route::post('/api/chat', [ChatController::class, 'ask'])->middleware('throttle:30,1');

Route::get('/login', [AuthController::class, 'show'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout', [AuthController::class, 'logout']);

Route::prefix('admin')->middleware(['auth', AdminOnly::class])->group(function () {
    Route::view('/', 'admin');
    Route::prefix('api')->controller(AdminController::class)->group(function () {
        Route::get('entries', 'entries'); Route::post('entries', 'storeEntry');
        Route::put('entries/{entry}', 'updateEntry'); Route::delete('entries/{entry}', 'destroyEntry');
        Route::get('documents', 'documents'); Route::post('documents', 'uploadDocument'); Route::delete('documents/{document}', 'destroyDocument');
        Route::get('categories', 'categories'); Route::post('categories', 'storeCategory');
        Route::get('synonyms', 'synonyms'); Route::post('synonyms', 'storeSynonym'); Route::delete('synonyms/{synonym}', 'destroySynonym');
        Route::get('unanswered', 'unanswered'); Route::get('logs', 'logs'); Route::get('analytics', 'analytics');
    });
});
