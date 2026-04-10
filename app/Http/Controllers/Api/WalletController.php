<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function byUser($user_id)
    {
        $wallet = Wallet::where('user_id', $user_id)->first();

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found for this user',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Wallet fetched',
            'data' => $wallet
        ]);
    }

    public function addMoney(Request $request)
{
    $validator = \Validator::make($request->all(), [
        'user_id' => 'required|integer|exists:users,id',
        'amount' => 'required|numeric|min:1'
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

        $wallet = Wallet::where('user_id', $request->user_id)->lockForUpdate()->first();
        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found for this user',
                'data' => null
            ], 404);
        }

        $wallet->available_balance = $wallet->available_balance + $request->amount;
        $wallet->save();

        Transaction::create([
            'user_id' => $request->user_id,
            'type' => 'ADD_MONEY',
            'amount' => $request->amount,
            'status' => 'SUCCESS',
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Money added successfully',
            'data' => [
                'user_id' => $wallet->user_id,
                'available_balance' => $wallet->available_balance,
                'frozen_balance' => $wallet->frozen_balance
            ]
        ], 200);

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
