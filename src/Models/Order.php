<?php

namespace DarkCoder\Ofa\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'ofa_billing_orders';

    protected $fillable = [
        'user_id',
        'plan_id',
        'total_amount',
        'status',
        'payment_method',
        'transaction_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
