<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;

class WalletController extends Controller
{
    public function byUser($user_id)
    {
        // If your wallets table uses user_id, this is correct.
        // If it uses something else (like owner_id), change it.
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
}
