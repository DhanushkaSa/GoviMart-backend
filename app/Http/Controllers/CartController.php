<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class CartController extends Controller
{
    // Add product to cart
    public function addToCart(Request $request, $productId)
    {
        $userId = Auth::id();

        $cart = Cart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($cart) {
            $cart->increment('quantity');
        } else {
            Cart::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => 1,
            ]);
        }
        $totalCount = Cart::where('user_id', $userId)->sum('quantity');
        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'count' => $totalCount
        ]);
    }

    // Get all cart items for the logged-in user
    public function viewCart()
    {
        $userId = Auth::id();

        $cartItems = Cart::with('product')
            ->where('user_id', $userId)
            ->get();

        return response()->json([
            'success' => true,
            'cartItems' => $cartItems,
        ]);
    }

    // Optional: Remove item from cart
    public function removeFromCart($productId)
    {
        $userId = Auth::id();

        $cartItem = Cart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $cartItem->delete();
            return response()->json(['success' => true, 'message' => 'Product removed from cart']);
        }

        return response()->json(['success' => false, 'message' => 'Product not found in cart'], 404);
    }

    public function createPaymentIntent(Request $request)
{
    $amount = $request->amount;
    //  dd(env('STRIPE_SECRET_KEY'));


    // Validation FIRST
    if (!$amount || $amount <= 0) {
        return response()->json([
            'status' => 400,
            'message' => 'Amount must be greater than 0'
        ], 400);
    }

    try {
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $paymentIntent = PaymentIntent::create([
            'amount' => $amount * 100, // convert to cents
            'currency' => 'usd',
            'payment_method_types' => ['card'],
        ]);

        return response()->json([
            'status' => 200,
            'clientSecret' => $paymentIntent->client_secret,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Stripe error: ' . $e->getMessage()
        ], 500);
    }
}


    public function clearCart(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // Delete all items from the user's cart
        $CartAll = Cart::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'Cart cleared successfully'
        ], 200);
    }
}
