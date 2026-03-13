<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function show($id)
    {
        $order = Order::where('id', $id)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order fetched',
            'data' => $order
        ]);
    }

    public function history($id)
    {
        $history = OrderStatusHistory::where('order_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Order history fetched',
            'data' => $history
        ]);
    }
}
