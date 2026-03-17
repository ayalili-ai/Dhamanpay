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
        $history = OrderStatusHistory::where('order_id', $id)
            ->orderBy('changed_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Order history fetched',
            'data' => $history
        ]);
    }
    public function confirm($id){
        try {
            $userId = 1; // temporary for testing

            \DB::select('SELECT escrow_freeze(?, ?)', [$id, $userId]);

            return response()->json([
                'success' => true,
                'message' => 'Order confirmed and escrow frozen'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function ship($id){
        try {
            $courierId = 1; // temporary for testing

            \DB::select('SELECT order_ship(?, ?)', [$id, $courierId]);

            return response()->json([
                'success' => true,
                'message' => 'Order shipped successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function submitProof($id){
        try {
            $courierId = 1; // temporary for testing
            $proofUrl = 'https://example.com/proof.jpg';

            \DB::select(
                'SELECT order_submit_proof(?, ?, ?)',
                [$id, $courierId, $proofUrl]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Delivery proof submitted'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);

        }
    }

    public function dispute($id){
        try {

            $userId = 1; // temporary for testing
            $reason = 'NOT_RECEIVED'; // temporary for testing

            \DB::select(
                'SELECT dispute_open(?, ?, ?)',
                [$id, $userId, $reason]
            );

            return response()->json([
                'success' => true,
                'message' => 'Dispute opened successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function release($id){
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
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function refund($id){
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
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function rejectDispute($id){
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
                'message' => $e->getMessage()
            ], 500);
        }
    }


    
    
}

