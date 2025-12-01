<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'status',
        'payment_status',
        'name',
        'location',
        'phone_number',
        'email',
        'payment_method',
        'total_usd',
        'total_riel',
        'shipping',
        'discount',
        // other fields you allow
    ];
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
