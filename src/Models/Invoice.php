<?php

namespace DarkCoder\Ofa\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'ofa_billing_invoices';

    protected $fillable = [
        'order_id',
        'invoice_number',
        'subtotal',
        'tax',
        'total',
        'paid_at',
        'pdf_path',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
