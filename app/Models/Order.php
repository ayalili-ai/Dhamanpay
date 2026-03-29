<?php

namespace App\Models;
use App\Models\User;
use App\Models\OrderStatusHistory;
use App\Models\Transaction;
use App\Models\DeliveryProof;
use App\Models\Dispute;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $guarded = [];
    
    // Order belongs to 3 users (roles)
public function customer()
{
    return $this->belongsTo(User::class, 'customer_id');
}

public function merchant()
{
    return $this->belongsTo(User::class, 'merchant_id');
}

public function courier()
{
    return $this->belongsTo(User::class, 'courier_id');
}

// Order has many history + transactions
public function statusHistory()
{
    return $this->hasMany(OrderStatusHistory::class, 'order_id');
}

public function transactions()
{
    return $this->hasMany(Transaction::class, 'order_id');
}

// Order has one proof + dispute
public function deliveryProof()
{
    return $this->hasOne(DeliveryProof::class, 'order_id');
}

public function dispute()
{
    return $this->hasOne(Dispute::class, 'order_id');
}

protected $fillable = [
    'order_code',
    'customer_id',
    'merchant_id',
    'courier_id',
    'amount',
    'status',
    'delivery_address',
    'proof_pdf',
];

}
