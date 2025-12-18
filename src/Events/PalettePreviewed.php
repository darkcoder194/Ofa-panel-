<?php

namespace DarkCoder\Ofa\Events;

use DarkCoder\Ofa\Models\ThemePalette;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PalettePreviewed implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $palette;

    public function __construct(ThemePalette $palette)
    {
        $this->palette = $palette->only(['id', 'name', 'slug', 'colors']);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('ofa.admin');
    }

    public function broadcastWith()
    {
        return $this->palette;
    }
}
