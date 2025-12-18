<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use DarkCoder\Ofa\Models\ThemePalette;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_theme_page_requires_authentication()
    {
        $res = $this->get('/admin/ofa');
        $res->assertRedirect('/login');
    }

    public function test_admin_can_view_theme_page()
    {
        $user = \App\Models\User::factory()->create(['root_admin' => 1]);

        $res = $this->actingAs($user)->get('/admin/ofa');
        $res->assertOk();
        // The admin app mounts into #ofa-theme-app and contains helpful copy server-side
        $res->assertSee('ofa-theme-app');
    }
}
