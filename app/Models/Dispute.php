<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    protected $table = 'disputes';
    protected $primaryKey = 'order_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $guarded = [];
    

}
