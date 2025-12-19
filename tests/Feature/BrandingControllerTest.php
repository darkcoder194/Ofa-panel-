<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use DarkCoder\Ofa\Models\OfaSetting;

class BrandingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_update_branding()
    {
        $user = \App\Models\User::factory()->create(['root_admin' => 0]);

        $res = $this->actingAs($user)->post('/admin/ofa/branding', ['site_name' => 'X']);
        $res->assertStatus(403);
    }

    public function test_admin_can_update_branding()
    {
        $user = \App\Models\User::factory()->create(['root_admin' => 1]);

        $res = $this->actingAs($user)->post('/admin/ofa/branding', ['site_name' => 'My Site']);
        $res->assertOk();
        $this->assertDatabaseHas('ofa_settings', ['key' => 'branding.site_name', 'value' => 'My Site']);
    }
}
