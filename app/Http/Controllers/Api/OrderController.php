<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Dispute;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    //this function fetches all order whith selected fields:
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $orders = Order::select(
                'id',
                'order_code',
                'status',
                'amount',
                'customer_id',
                'merchant_id',
                'courier_id'
            )->orderBy('id', 'desc')->get();
        } elseif ($user->role === 'customer') {
            $orders = Order::select(
                'id',
                'order_code',
                'status',
                'amount',
                'customer_id',
                'merchant_id',
                'courier_id'
            )->where('customer_id', $user->id)
             ->orderBy('id', 'desc')
             ->get();
        } elseif ($user->role === 'merchant') {
            $orders = Order::select(
                'id',
                'order_code',
                'status',
                'amount',
                'customer_id',
                'merchant_id',
                'courier_id'
            )->where('merchant_id', $user->id)
             ->orderBy('id', 'desc')
             ->get();
        } elseif ($user->role === 'courier') {
            $orders = Order::select(
                'id',
                'order_code',
                'status',
                'amount',
                'customer_id',
                'merchant_id',
                'courier_id'
            )->where('courier_id', $user->id)
             ->orderBy('id', 'desc')
             ->get();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized role'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Orders fetched successfully',
            'data' => $orders
        ]);
    
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $allowed =
            $user->role === 'admin' ||
            $order->customer_id === $user->id ||
            $order->merchant_id === $user->id ||
            $order->courier_id === $user->id;

        if (!$allowed) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: you cannot view this order'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order fetched',
            'data' => $order
        ]);
    }

    public function history(Request $request, $id)
    {
        $user = $request->user();

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $allowed =
            $user->role === 'admin' ||
            $order->customer_id === $user->id ||
            $order->merchant_id === $user->id ||
            $order->courier_id === $user->id;

        if (!$allowed) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: you cannot view this order history'
            ], 403);
        }

        $history = OrderStatusHistory::where('order_id', $id)
            ->orderBy('changed_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Order history fetched',
            'data' => $history
        ]);
    }       
    public function confirm(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'customer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: customer only'
            ], 403);
        }

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        if ($order->customer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: not your order'
            ], 403);
        }

        try {
            DB::select('SELECT escrow_freeze(?, ?)', [
                $order->id,
                $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order confirmed and escrow frozen'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Function failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function ship(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'courier') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: courier only'
            ], 403);
        }

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        try {
            \DB::select('SELECT order_ship(?, ?)', [$id, $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Order shipped successfully'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Function failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function submitProof(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'courier') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: courier only'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'proof_url' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'error' => $validator->errors()
            ], 422);
        }

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        try {
            \DB::select('SELECT order_submit_proof(?, ?, ?)',[$id, $user->id, $request->proof_url]);

            return response()->json([
                'success' => true,
                'message' => 'Delivery proof submitted'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Function failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function dispute(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'customer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: customer only'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|in:NOT_RECEIVED,DAMAGED,NOT_SAME_ITEM,OTHER',
            'dispute_description' => 'nullable|string',
            'received_serial_number' => 'nullable|string',
            'unboxing_video_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'error' => $validator->errors()
            ], 422);
        }

        if (
            $request->reason === 'NOT_SAME_ITEM' &&
            (
                !$request->filled('received_serial_number') ||
                !$request->filled('unboxing_video_url')
            )
        ) {

            return response()->json([
                'success' => false,
                'message' => 'received_serial_number and unboxing_video_url are required for NOT_SAME_ITEM'
            ], 422);
        }

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        if ($order->customer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: not your order'
            ], 403);
        }

        try {
            $result = DB::select(
                'SELECT * FROM open_dispute_v2(?, ?, ?, ?, ?, ?)',
                [
                    $order->id,
                    $user->id,
                    $request->reason,
                    $request->dispute_description,
                    $request->received_serial_number,
                    $request->unboxing_video_url
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Dispute opened successfully',
                'data' => $result[0] ?? null
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Function failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function release(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: admin only'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'admin_note' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'error' => $validator->errors()
            ], 422);
        }

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        try {
            $result = DB::select(
                'SELECT * FROM escrow_release_v3(?, ?, ?)',
                [
                    $order->id,
                    $user->id,
                    $request->admin_note
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Escrow released successfully',
                'data' => $result[0] ?? null
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Function failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function refund(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: admin only'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'admin_note' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'error' => $validator->errors()
            ], 422);
        }

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        try {
            $result = DB::select(
                'SELECT * FROM escrow_refund_v2(?, ?, ?)',
                [
                    $order->id,
                    $user->id,
                    $request->admin_note
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'data' => $result[0] ?? null
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Function failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'merchant') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: merchant only'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'delivery_address' => 'required|string',
            'product_name' => 'required|string',
            'expected_serial_number' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'error' => $validator->errors()
            ], 422);
        }

        try {
            $result = DB::select(
                'SELECT * FROM create_order_by_merchant_v2(?, ?, ?, ?, ?, ?)',
                [
                    $user->id,
                    $request->customer_id,
                    $request->amount,
                    $request->delivery_address,
                    $request->product_name,
                    $request->expected_serial_number
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $result[0] ?? null
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Function failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }   
    public function listDisputes(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {

            $disputes = Dispute::orderBy('created_at', 'desc')->get();

        } elseif ($user->role === 'customer') {

            $disputes = Dispute::whereHas('order', function ($query) use ($user) {
                $query->where('customer_id', $user->id);
            })->orderBy('created_at', 'desc')->get();

        } elseif ($user->role === 'merchant') {

            $disputes = Dispute::whereHas('order', function ($query) use ($user) {
                $query->where('merchant_id', $user->id);
            })->orderBy('created_at', 'desc')->get();

        } elseif ($user->role === 'courier') {

            $disputes = Dispute::whereHas('order', function ($query) use ($user) {
                $query->where('courier_id', $user->id);
            })->orderBy('created_at', 'desc')->get();

        } else {

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized role'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Disputes fetched successfully',
            'data' => $disputes
        ], 200);
    }   

    public function cancel(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'customer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: customer only'
            ], 403);
        }

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        if ($order->customer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: not your order'
            ], 403);
        }

        try {
            $result = DB::select(
                'SELECT * FROM cancel_order_by_customer(?, ?)',
                [
                    $order->id,
                    $user->id
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $result[0] ?? null
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Function failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
}

