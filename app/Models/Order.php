<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'driver_id',
        'from_address',
        'to_address',
        'price',
        'vechicle_type',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
