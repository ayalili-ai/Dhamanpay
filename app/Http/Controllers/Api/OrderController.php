<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    //this function fetches all order whith selected fields:
    public function index(){
        $orders = Order::select(
            'id',
            'order_code',
            'status',
            'amount'
        )->get();

        return response()->json([
            'success' => true,
            'message' => 'Orders fetched successfully',
            'data' => $orders
        ]);
    }

    public function show($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
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
        //show the history of an order
    
        $order = Order::find($id);

        if (!$order) {//check if the order exist 
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
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
    {///confirm the order
    
        $user = $request->user();

        if ($user->role !== 'merchant') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: merchant only'
            ], 403);
        }

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
             'success' => false,
             'message' => 'Order not found'
            ], 404);
        }
        
        if ($order->merchant_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: not your order'
            ], 403);
        }

        try {

            \DB::select('SELECT escrow_freeze(?, ?)', [$order->id, $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Order confirmed and escrow frozen'
            ]);

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

        $validator = \Validator::make($request->all(), [
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

        $validator = \Validator::make($request->all(), [
            'reason' => 'required|in:NOT_RECEIVED,DAMAGED,OTHER'
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

        if ($order->customer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: not your order'
            ], 403);
        }

        try {
            \DB::select('SELECT dispute_open(?, ?, ?)',[$id, $user->id, $request->reason]);

            return response()->json([
                'success' => true,
                'message' => 'Dispute opened successfully'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Function failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function release(Request $request,$id)
    {   
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: admin only'
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
            

            \DB::select(
                'SELECT escrow_release(?, ?)',
                [$id, $user->id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Escrow released successfully'
            ]);
        } 
        catch (\Throwable $e) {
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

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        try {
            \DB::select(
                'SELECT escrow_refund(?, ?)',[$id, $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Function failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function rejectDispute(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: admin only'
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
            $note = 'Dispute rejected after review';

            \DB::select(
                'SELECT dispute_reject(?, ?, ?)',
                [$id, $user->id, $note]
            );

            return response()->json([
                'success' => true,
                'message' => 'Dispute rejected successfully'
            ]);
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
    $validator = \Validator::make($request->all(), [
        'order_code' => 'required|string|unique:orders,order_code',
        'customer_id' => 'required|integer|exists:users,id',
        'merchant_id' => 'required|integer|exists:users,id',
        'amount' => 'required|numeric|min:1',
        'delivery_address' => 'required|string'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'error' => $validator->errors()
        ], 422);
    }

    try {
        DB::beginTransaction();

        $customer = User::find($request->customer_id);
        $merchant = User::find($request->merchant_id);

        // ❌ same user
        if ($customer->id == $merchant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Customer and merchant cannot be the same user'
            ], 422);
        }

        // wrong roles
        if ($customer->role !== 'customer') {
            return response()->json([
                'success' => false,
                'message' => 'customer_id must be a customer'
            ], 422);
        }

        if ($merchant->role !== 'merchant') {
            return response()->json([
                'success' => false,
                'message' => 'merchant_id must be a merchant'
            ], 422);
        }

        //  create order
        $order = Order::create([
            'order_code' => $request->order_code,
            'customer_id' => $customer->id,
            'merchant_id' => $merchant->id,
            'amount' => $request->amount,
            'status' => 'CREATED',
            'delivery_address' => $request->delivery_address
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => $order
        ], 201);

    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Function failed',
            'error' => $e->getMessage()
        ], 500);
    }
}


    
    
}

