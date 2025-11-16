<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    // List all products
    public function index()
    {
        $products = Product::all();
        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

    // Show a single product
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json(['success' => true, 'product' => $product]);
    }

    // Create a new product
    // Create a new product
    public function store(Request $request)
    {
        // This validates the data and stores it in a variable
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

        // --- START: THIS IS THE NEW BLOCK ---

        // 1. Check if an image file was uploaded
        $imagePath = null;
        if ($request->hasFile('image')) {

            // 2. THIS IS THE LINE YOU ASKED ABOUT:
            // It saves the file and gets the path.
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // 3. Prepare all the text data from validation
        // (We use $validatedData instead of $request->all() for security)
        $dataToCreate = $validatedData;

        // 4. Add the image path (or null) to the data
        $dataToCreate['image'] = $imagePath;

        // 5. Create the product using this new, correct data
        $product = Product::create($dataToCreate);

        // --- END: NEW BLOCK ---

        return response()->json(['success' => true, 'product' => $product], 201); // 201 means "Created"
    }

    // Update a product
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $product->update($request->all());

        return response()->json(['success' => true, 'product' => $product]);
    }

    // Delete a product
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted']);
    }
}
