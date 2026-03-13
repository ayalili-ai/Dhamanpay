use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WalletController;

Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::get('/orders/{id}/history', [OrderController::class, 'history']);
Route::get('/wallets/{user_id}', [WalletController::class, 'byUser']);
