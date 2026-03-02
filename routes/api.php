<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\ImageController;

Route::prefix('v1')->group(function () {

    // ─── Health Check Eureka ─────────────────────────
    Route::get('health', function () {
        return response()->json([
            'status'  => 'UP',
            'service' => env('APP_NAME'),
            'version' => '1.0.0',
            'swagger' => url('/api/documentation'),
        ]);
    });

    // ─── Routes publiques ───────────────────────────
    Route::get('search', [SearchController::class, 'search']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{slug}', [ProductController::class, 'show']);
    Route::get('products/{id}/related', [ProductController::class, 'related']);
    Route::get('products/{id}/images', [ImageController::class, 'index']);
    Route::get('products/{id}/reviews', [ReviewController::class, 'index']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{slug}', [CategoryController::class, 'show']);
    Route::get('categories/{slug}/products', [CategoryController::class, 'products']);

    // ─── Routes protégées ───────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Produits
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);

        // Catégories
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{id}', [CategoryController::class, 'update']);
        Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

        // Avis
        Route::post('products/{id}/reviews', [ReviewController::class, 'store']);
        Route::put('reviews/{id}/approve', [ReviewController::class, 'approve']);
        Route::delete('reviews/{id}', [ReviewController::class, 'destroy']);

        // Images
        Route::post('products/{id}/images', [ImageController::class, 'upload']);
        Route::delete('products/{id}/images/{imageId}', [ImageController::class, 'destroy']);
        Route::put('products/{id}/images/{imageId}/primary', [ImageController::class, 'setPrimary']);
    });
});
