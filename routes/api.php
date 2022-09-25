<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;


///////////////////////////////////////////////////////////////////////////// AUTH
Route::group(
    ['prefix' => '/auth'],
    function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    }
);


//////////////////////////////////////////////////////////////////////////// PUBLIC
Route::group(
    [
        'prefix' => '/category',
        'controller' => CategoryController::class,
    ],
    function () {
        Route::get('/', 'allCategories');
        Route::get('/{slug}', 'oneCategory');
    }
);

Route::group(
    [
        'prefix' => '/product',
        'controller' => ProductController::class,
    ],
    function () {
        Route::get('/', 'allProducts');
        Route::get('/{slug}', 'oneProduct');
    }
);


////////////////////////////////////////////////////////////////////////// PRIVATE 
Route::group(
    [
        'middleware' => ['auth:sanctum']
    ],
    function () {
        Route::any('/auth/logout', [AuthController::class, 'logout']);

        Route::group(
            [
                'prefix' => '/category',
                'controller' => CategoryController::class,
            ],
            function () {
                Route::post('/',  'createCategory');
                Route::post('/{id}', 'updateCategory');
                Route::delete('/{id}', 'deleteCategory');
            }
        );

        Route::group(
            [
                'prefix' => '/product',
                'controller' => ProductController::class,
            ],
            function () {
                Route::post('/',  'createProduct');
                Route::post('/{id}', 'updateProduct');
                Route::delete('/{id}', 'deleteProduct');
            }
        );
    }
);
