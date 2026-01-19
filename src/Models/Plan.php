<?php

namespace DarkCoder\Ofa\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'ofa_billing_plans';

    protected $fillable = [
        'name',
        'description',
        'cpu',
        'memory',
        'disk',
        'price',
        'billing_period',
        'is_active',
        'features',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function nodes()
    {
        return $this->belongsToMany(Node::class, 'ofa_plan_nodes');
    }
}
