<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function getFarmerOrders(Request $request)
    {
        $farmerId = $request->user()->id;

        $orders = OrderItem::with('order.buyer', 'product')
            ->where('farmer_id', $farmerId)
            ->get()
            ->groupBy('order_id');

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }
}
