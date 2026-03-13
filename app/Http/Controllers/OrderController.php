<?php

namespace App\Http\Controllers;
use App\Models\Order;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function show($id)
    {
        $order = Order::with([
            'customer',
            'merchant',
            'courier',
            'statusHistory',
            'transactions',
            'deliveryProof',
            'dispute'
        ])->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json($order);
    }
}
