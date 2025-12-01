<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'payer_name',
        'phone_number',
        'image_path',
        'status', 
    ];
}
