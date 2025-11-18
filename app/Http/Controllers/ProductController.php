<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // List all products
    public function index()
    {
        $products = Product::with('user')->get(); // Include user info if needed
        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

    // Show a single product
    public function show($id)
    {
        $product = Product::with('user')->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json(['success' => true, 'product' => $product]);
    }

    // Create a new product
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'brand' => 'nullable|string',
            'location' => 'nullable|string',
            'best_before' => 'nullable|date',
            'price' => 'required|numeric',
            'original_price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'rating' => 'nullable|numeric',
            'rating_count' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('products', 'public');
        }

        // Set the user_id from authenticated user
        $validatedData['user_id'] = Auth::id();

        $product = Product::create($validatedData);

        return response()->json(['success' => true, 'product' => $product], 201);
    }


    // Update a product
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        // Only the owner can update (optional but recommended)
        if ($product->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string',
            'brand' => 'nullable|string',
            'location' => 'nullable|string',
            'best_before' => 'nullable|date',
            'price' => 'sometimes|numeric',
            'original_price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'rating' => 'nullable|numeric',
            'rating_count' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validatedData);

        return response()->json(['success' => true, 'product' => $product]);
    }

    // Delete a product
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        // Only the owner can delete (optional)
        if ($product->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted']);
    }
}
