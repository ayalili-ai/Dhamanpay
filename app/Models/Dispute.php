<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class Dispute extends Model
{
    protected $table = 'disputes';
    protected $primaryKey = 'order_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $guarded = [];
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    

}
