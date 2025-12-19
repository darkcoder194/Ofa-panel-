<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use DarkCoder\Ofa\Models\OfaServerAction;

class ServerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_request_version_change()
    {
        $user = \App\Models\User::factory()->create(['root_admin' => 1]);

        $payload = ['server_uuid' => '123e4567-e89b-12d3-a456-426614174000', 'type' => 'version', 'payload' => ['version' => '1.18.1']];

        $res = $this->actingAs($user)->postJson('/admin/ofa/servers/request-change', $payload);
        $res->assertStatus(202);
        $this->assertDatabaseHas('ofa_server_actions', ['server_uuid' => $payload['server_uuid'], 'action_type' => 'version']);
    }

    public function test_non_admin_cannot_request_change()
    {
        $user = \App\Models\User::factory()->create(['root_admin' => 0]);
        $payload = ['server_uuid' => '123e4567-e89b-12d3-a456-426614174000', 'type' => 'egg', 'payload' => ['egg' => 'minecraft']];
        $res = $this->actingAs($user)->postJson('/admin/ofa/servers/request-change', $payload);
        $res->assertStatus(403);
    }
}
