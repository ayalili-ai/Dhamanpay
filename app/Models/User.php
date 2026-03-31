<?php

namespace App\Models;
use App\Models\Wallet;
use App\Models\Order;



// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    const UPDATED_AT = null;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'role',
        'store_name',
        'commercial_register',
        'wilaya',
        'delivery_type',
        'vehicle_matricule',
        'delivery_company',
        'admin_code',
        'card_number',
        'card_expiry',
        'latitude',
        'longitude',
        'rating',
        'password',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }
    public function wallet()
{
    return $this->hasOne(Wallet::class, 'user_id');
}

public function customerOrders()
{
    return $this->hasMany(Order::class, 'customer_id');
}


}
