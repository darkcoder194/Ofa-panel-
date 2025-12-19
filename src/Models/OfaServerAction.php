<?php

namespace DarkCoder\Ofa\Models;

use Illuminate\Database\Eloquent\Model;

class OfaServerAction extends Model
{
    protected $table = 'ofa_server_actions';
    protected $fillable = ['server_uuid', 'action_type', 'payload', 'status'];

    protected $casts = ['payload' => 'array'];
}
