<?php

namespace DarkCoder\Ofa\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $table = 'ofa_billing_wallet_transactions';

    protected $fillable = [
        'wallet_id',
        'amount',
        'type',
        'description',
        'reference_id',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
