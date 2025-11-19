<?php

// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use Chatify\Http\Controllers\Api\MessagesController;
use Illuminate\Support\Facades\Broadcast;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/products/all', [ProductController::class, 'allProducts']);
Route::patch('/admin/products/{id}/status', [ProductController::class, 'updateStatus']);
Route::get('/admin/products/all', [ProductController::class, 'allProductsForAdmin']);

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
    Route::delete('/cart/clear', [CartController::class, 'clearCart']);

    Route::get('/products', [ProductController::class, 'index']);       // List all products
    Route::get('/products/{id}', [ProductController::class, 'show']);   // Get single product
    Route::post('/products', [ProductController::class, 'store']);      // Add product
    Route::put('/products/{id}', [ProductController::class, 'update']); // Update product
    Route::delete('/products/{id}', [ProductController::class, 'destroy']); // Delete product
    // List all products



    Route::post('/broadcasting/auth', function (Request $request) {
        // Validate bearer token manually
        // $user = auth('sanctum')->user();
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return Broadcast::auth($request);
    });




    Route::post('/chat/auth', [MessagesController::class, 'pusherAuth'])->name('api.pusher.auth');
    Route::post('/idInfo', [MessagesController::class, 'idFetchData'])->name('api.idInfo');
    Route::post('/sendMessage', [MessagesController::class, 'send'])->name('api.send.message');
    Route::post('/fetchMessages', [MessagesController::class, 'fetch'])->name('api.fetch.messages');
    Route::get('/download/{fileName}', [MessagesController::class, 'download'])->name('api.download');
    Route::post('/makeSeen', [MessagesController::class, 'seen'])->name('api.messages.seen');
    Route::get('/getContacts', [MessagesController::class, 'getContacts'])->name('api.contacts.get');
    Route::post('/star', [MessagesController::class, 'favorite'])->name('api.star');
    Route::post('/favorites', [MessagesController::class, 'getFavorites'])->name('api.favorites');
    Route::get('/search', [MessagesController::class, 'search'])->name('api.search');
    Route::post('/shared', [MessagesController::class, 'sharedPhotos'])->name('api.shared');
    Route::post('/deleteConversation', [MessagesController::class, 'deleteConversation'])->name('api.conversation.delete');
    Route::post('/updateSettings', [MessagesController::class, 'updateSettings'])->name('api.avatar.update');
    Route::post('/setActiveStatus', [MessagesController::class, 'setActiveStatus'])->name('api.activeStatus.set');
});
