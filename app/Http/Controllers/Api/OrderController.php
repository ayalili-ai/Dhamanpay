<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;

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

    public function ship($id)
    {///ship the order 

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        try {
            $courierId = 1; // temporary for testing until i build authentification 

            \DB::select('SELECT order_ship(?, ?)', [$id, $courierId]);

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

    public function submitProof($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        try {
            $courierId = 1; // temporary for testing until auth
            $proofUrl = 'https://example.com/proof.jpg';

            \DB::select(
                'SELECT order_submit_proof(?, ?, ?)',
                [$id, $courierId, $proofUrl]
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
    public function release($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        try {
            $adminId = 1; // temporary for testing

            \DB::select(
                'SELECT escrow_release(?, ?)',
                [$id, $adminId]
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

    public function refund($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        try {
            $adminId = 1; // temporary for testing

            \DB::select(
                'SELECT escrow_refund(?, ?)',
                [$id, $adminId]
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

    public function rejectDispute($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        try {
            $adminId = 1; // temporary for testing
            $note = 'Dispute rejected after review'; // temporary for testing

            \DB::select(
                'SELECT dispute_reject(?, ?, ?)',
                [$id, $adminId, $note]
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
        $request->validate([
            'order_code' => 'required|string',
            'customer_id' => 'required|integer',
            'merchant_id' => 'required|integer',
            'courier_id' => 'nullable|integer',
            'amount' => 'required|numeric|min:1',
            'delivery_address' => 'required|string'
        ]);

        try {
            $order = Order::create([
                'order_code' => $request->order_code,
                'customer_id' => $request->customer_id,
                'merchant_id' => $request->merchant_id,
                'courier_id' => $request->courier_id,
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

