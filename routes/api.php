<?php

// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        // This will now also return the 'role' and 'vendor' info if it exists
        return $request->user()->load('vendor');
    });

    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::put('/profile', [ProfileController::class, 'updateProfile']); // <-- ADDED (For saving data)
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']); // <-- ADDED
    Route::post('/profile/photo', [ProfileController::class, 'updateProfilePhoto']); // <-- ADDED

    Route::post('/cart/add/{productId}', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'viewCart']);
    Route::delete('/cart/remove/{productId}', [CartController::class, 'removeFromCart']);
    Route::post('/cart/create-payment-intent', [CartController::class, 'createPaymentIntent']);

    Route::get('/products', [ProductController::class, 'index']);       // List all products
    Route::get('/products/{id}', [ProductController::class, 'show']);   // Get single product
    Route::post('/products', [ProductController::class, 'store']);      // Add product
    Route::put('/products/{id}', [ProductController::class, 'update']); // Update product
    Route::delete('/products/{id}', [ProductController::class, 'destroy']); // Delete product
});
