<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * @group Others
 */
Route::get('/', function () {
    return response()->json(['message' => 'Welcome to API what-to-watch']);
});


// Фильмы
Route::prefix('/films')->controller(FilmController::class)->middleware('optimal.auth')->group(function () {
    Route::get('/', 'index')->name('films.index');
    Route::get('/{film}', 'film')->name('film');
    Route::get('/{film}/similar', 'similar')->name('films.similar');

    Route::middleware(['auth:sanctum', 'film.policy'])->group(function () {
        Route::patch('/{film}', 'update')->name('films.update');
        Route::post('/', 'add')->name('films.add');
        Route::delete('/{film}', 'delete')->name('films.delete');
    });
});

// Промо-ролик
Route::get('/promo', [PromoController::class, 'index'])->name('promo.index');
Route::prefix('/promo')->controller(PromoController::class)->middleware(['auth:sanctum', 'can:user.isAdmin'])->group(
    function () {
        Route::post('/{film}', 'store')->name('set.promo');
        Route::delete('/{film}', 'destroy')->name('delete.promo');
    }
);

// Избранное
Route::middleware('auth:sanctum')->controller(FavoriteController::class)->group(function () {
    Route::get('/favorite', 'index')->name('favorite.index');
    Route::post('/films/{film}/favorite', 'add')->name('favorite.add');
    Route::delete('/films/{film}/favorite', 'delete')->name('favorite.delete');
});

// Комментарии
Route::get('/films/{film}/comments', [CommentController::class, 'show'])->name('comments.show');
Route::middleware(['auth:sanctum', 'comment.policy'])->controller(CommentController::class)->group(function () {
    Route::post('/films/{film}/comments', 'add')->name('comments.add');
    Route::patch('/comments/{comment}', 'update')->name('comments.update');
    Route::delete('/comments/{comment}', 'delete')->name('comments.delete');
});

// Пользователь
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register')->name('register');
    Route::middleware('auth:sanctum')->post('/logout', 'logout')->name('logout');
    Route::post('/login', 'login')->name('login');
});

Route::prefix('/user')->controller(UserController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/', 'show')->name('profile');
    Route::patch('/', 'update')->name('profile.update');
});

// Технические роуты
Route::middleware(['auth:sanctum', 'can:user.isAdmin'])
    ->apiResource('genres', GenreController::class)
    ->except(['index']);

Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
