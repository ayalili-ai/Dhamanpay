<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
    public function confirm($id)
    {///confirm the order 
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
             'success' => false,
             'message' => 'Order not found'
            ], 404);
        }

        try {

            \DB::select('SELECT escrow_freeze(?, ?)', [$order->id, $order->customer_id]);

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

    public function ship(Request $request,$id)
    {///ship the order 
        $validator = \Validator::make($request->all(), [
            'courier_id' => 'required|integer'
        ]);
        /// what if we dont insert courier id 
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
            
            \DB::select('SELECT order_ship(?, ?)', [$id,$request->courier_id]);

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

    public function submitProof(Request $request,$id)
    {   
        $validator = \Validator::make($request->all(), [
            'courier_id' => 'required|integer',
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

            \DB::select(
                'SELECT order_submit_proof(?, ?, ?)',
                [$id,  $request->courier_id, $request->proof_url]
            );
            
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

    public function dispute($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        try {
            \DB::select(
                'SELECT dispute_open(?, ?, ?)',
                [$order->id, $order->customer_id, 'NOT_RECEIVED']
            );

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
        $validator = \Validator::make($request->all(), [
            'admin_id' => 'required|integer'
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
            

            \DB::select(
                'SELECT escrow_release(?, ?)',
                [$id, $request->admin_id]
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

    public function refund(Request $request,$id)
    {   
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|integer'
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

            \DB::select(
                'SELECT escrow_refund(?, ?)',
                [$id, $request->admin_id]
            );

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

    public function rejectDispute(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'error' => $validator->errors()
            ], 422);
        }

        $order = Order::find( $id);

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
                [$id, $request->admin_id, $note]
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
            'order_code' => 'required|string',
            'customer_id' => 'required|integer',
            'merchant_id' => 'required|integer',
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
            $order = Order::create([
                'order_code' => $request->order_code,
                'customer_id' => $request->customer_id,
                'merchant_id' => $request->merchant_id,
                'amount' => $request->amount,
                'status' => 'CREATED', // KEEP THIS
                'delivery_address' => $request->delivery_address
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order created',
                'data' => $order
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Function failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    
    
}

