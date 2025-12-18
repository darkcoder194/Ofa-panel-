<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use DarkCoder\Ofa\Models\ThemePalette;

class ThemeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_authentication()
    {
        $res = $this->get('/admin/ofa/themes');
        $res->assertRedirect('/login');
    }

    public function test_admin_can_view_palettes()
    {
        $user = \App\Models\User::factory()->create(['root_admin' => 1]);
        ThemePalette::factory()->create(['name' => 'Test', 'slug' => 'test', 'colors' => ['primary' => '#000000']]);

        $res = $this->actingAs($user)->get('/admin/ofa/themes');
        $res->assertOk();
        $res->assertJsonCount(1);
    }

    public function test_admin_can_create_palette()
    {
        $user = \App\Models\User::factory()->create(['root_admin' => 1]);

        $payload = [
            'name' => 'New Theme',
            'slug' => 'new-theme',
            'colors' => ['primary' => '#123456']
        ];

        $res = $this->actingAs($user)->post('/admin/ofa/themes', $payload);
        $res->assertStatus(201);
        $this->assertDatabaseHas('ofa_theme_palettes', ['slug' => 'new-theme']);
    }

    public function test_admin_can_set_default()
    {
        $user = \App\Models\User::factory()->create(['root_admin' => 1]);

        $a = ThemePalette::factory()->create(['slug' => 'a', 'is_default' => true]);
        $b = ThemePalette::factory()->create(['slug' => 'b', 'is_default' => false]);

        $res = $this->actingAs($user)->post("/admin/ofa/themes/{$b->id}/default");
        $res->assertOk();

        $this->assertDatabaseHas('ofa_theme_palettes', ['id' => $b->id, 'is_default' => 1]);
        $this->assertDatabaseHas('ofa_theme_palettes', ['id' => $a->id, 'is_default' => 0]);
    }

    public function test_admin_can_preview_and_clear_preview()
    {
        $user = \App\Models\User::factory()->create(['root_admin' => 1]);

        $p = ThemePalette::factory()->create(['slug' => 'pv', 'colors' => ['primary' => '#abcdef']]);

        $res = $this->actingAs($user)->post("/admin/ofa/themes/{$p->id}/preview");
        $res->assertOk();
        $res->assertSessionHas('ofa_preview_palette', $p->id);

        $res2 = $this->actingAs($user)->post('/admin/ofa/preview/clear');
        $res2->assertStatus(204);
        $this->actingAs($user)->get('/admin/ofa')->assertDontSee('ofa_preview_palette');
    }

    public function test_admin_can_delete_palette()
    {
        $user = \App\Models\User::factory()->create(['root_admin' => 1]);
        $p = ThemePalette::factory()->create(['slug' => 'del-me']);

        $res = $this->actingAs($user)->delete("/admin/ofa/themes/{$p->id}");
        $res->assertStatus(204);
        $this->assertDatabaseMissing('ofa_theme_palettes', ['id' => $p->id]);
    }

    public function test_admin_can_update_palette()
    {
        $user = \App\Models\User::factory()->create(['root_admin' => 1]);
        $p = ThemePalette::factory()->create(['slug' => 'up-me', 'name' => 'Old', 'colors' => ['primary' => '#111111']]);

        $payload = ['name' => 'Updated', 'colors' => ['primary' => '#222222']];
        $res = $this->actingAs($user)->patch("/admin/ofa/themes/{$p->id}", $payload);
        $res->assertOk();
        $this->assertDatabaseHas('ofa_theme_palettes', ['id' => $p->id, 'name' => 'Updated']);
    }

    public function test_admin_can_export_palette()
    {
        $user = \App\Models\User::factory()->create(['root_admin' => 1]);
        $p = ThemePalette::factory()->create(['slug' => 'exp', 'colors' => ['primary' => '#000000']]);

        $res = $this->actingAs($user)->get("/admin/ofa/themes/{$p->id}/export");
        $res->assertOk();
        $content = json_decode($res->getContent(), true);
        $this->assertEquals('exp', $content['slug']);
    }

    public function test_admin_can_import_palette()
    {
        $user = \App\Models\User::factory()->create(['root_admin' => 1]);

        $payload = [
            'name' => 'Imported',
            'slug' => 'imported',
            'colors' => ['primary' => '#111111']
        ];

        $res = $this->actingAs($user)->postJson('/admin/ofa/themes/import', $payload);
        $res->assertStatus(201);
        $this->assertDatabaseHas('ofa_theme_palettes', ['slug' => 'imported']);
    }
}

