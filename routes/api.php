<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\TagController;
use App\Http\Controllers\API\V1\PostController;
use App\Http\Controllers\API\V1\LoginController;
use App\Http\Controllers\API\V1\CategoryController;
use App\Http\Controllers\API\V1\CommentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::fallback(function () {
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact info@website.com'
    ], 404);
});

Route::group(['middleware' => 'web'], function () {
    Route::get('api/docs', '\L5Swagger\Http\Controllers\SwaggerController@api')->name('l5swagger.api');
});

Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
    Route::get('login', [LoginController::class, 'login'])->name('login');
    Route::post('register', [LoginController::class, 'register'])->name('register');
    Route::post('refresh-token', [LoginController::class, 'refreshToken'])->name('refreshToken');
});

Route::group(['prefix' => 'category', 'as' => 'category.', 'middleware' => ['auth:api']], function () {
    Route::get('/', [CategoryController::class, 'show'])->name('list');
    Route::post('/add', [CategoryController::class, 'add'])->name('add');
    Route::post('/edit/{cat_id}', [CategoryController::class, 'edit'])->name('edit');
    Route::post('delete/{cat_id}', [CategoryController::class, 'delete'])->name('delete');
});

Route::group(['prefix' => 'post', 'as' => 'post.', 'middleware' => ['auth:api']], function () {
    Route::get('/', [PostController::class, 'show'])->name('list');
    Route::post('add', [PostController::class, 'add'])->name('add');
    Route::post('edit/{post_id}', [PostController::class, 'update'])->name('update');
    Route::post('delete/{post_id}', [PostController::class, 'delete'])->name('delete');
});

Route::group(['prefix' => 'tag', 'as' => 'tag.', 'middleware' => ['auth:api']], function () {
    Route::get('/search', [TagController::class, 'search'])->name('search');
});

Route::group(['prefix' => 'comment', 'as' => 'comment.', 'middleware' => ['auth:api']], function () {
    Route::get('/', [CommentController::class, 'show'])->name('list');
    Route::post('/add', [CommentController::class, 'add'])->name('add');
    Route::post('/edit/{comment_id}', [CommentController::class, 'edit'])->name('edit');
    Route::post('delete/{comment_id}', [CommentController::class, 'delete'])->name('delete');
});