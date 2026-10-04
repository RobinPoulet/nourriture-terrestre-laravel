<?php

namespace Tests\Feature;

use App\Enums\SettingKey;
use App\Models\Announcement;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        OrderTest::createMenu();

        $this->admin = User::query()->create([
            'name' => 'Robin',
            'creation_date' => '2026-01-01',
            'is_admin' => true,
            'password_hash' => Hash::make('mot-de-passe-solide'),
        ]);
    }

    public function test_admin_pages_require_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->post('/admin/create-user', ['name' => 'Eve'])->assertRedirect(route('admin.login'));

        $this->assertNull(User::query()->where('name', 'Eve')->first());
    }

    public function test_admin_can_log_in_and_out(): void
    {
        $this->post('/admin/authenticate', ['name' => 'Robin', 'password' => 'mot-de-passe-solide'])
            ->assertRedirect(route('admin.index'));
        $this->assertAuthenticatedAs($this->admin);

        $this->get('/admin')->assertOk()->assertSee('Administration');

        $this->post('/admin/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_wrong_password_or_non_admin_is_rejected(): void
    {
        $this->post('/admin/authenticate', ['name' => 'Robin', 'password' => 'mauvais'])->assertSessionHasErrors();
        $this->assertGuest();

        $this->admin->update(['is_admin' => false]);
        $this->post('/admin/authenticate', ['name' => 'Robin', 'password' => 'mot-de-passe-solide'])->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_demoted_admin_loses_access(): void
    {
        $this->actingAs($this->admin);
        $this->admin->update(['is_admin' => false]);

        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_admin_manages_users_announcements_and_settings(): void
    {
        $this->actingAs($this->admin);

        $this->post('/admin/create-user', ['name' => 'Eve'])->assertSessionHas('success');
        $eve = User::query()->where('name', 'Eve')->sole();

        $this->post("/admin/set-role/{$eve->id}")->assertSessionHas('success');
        $this->assertTrue($eve->fresh()->is_admin);

        $eve->update(['cookie_hash' => str_repeat('d', 64)]);
        $this->post("/admin/reset-device/{$eve->id}");
        $this->assertNull($eve->fresh()->cookie_hash);

        $this->post('/admin/create-user', ['name' => '  '])->assertSessionHasErrors();

        $this->post('/admin/create-announcement', ['message' => 'Nouveau : le vote !']);
        $announcement = Announcement::query()->sole();
        $this->get('/')->assertSee('Nouveau : le vote !');

        $this->post("/admin/toggle-announcement/{$announcement->id}");
        $this->assertFalse($announcement->fresh()->is_visible);

        $this->post('/admin/update-settings', ['force_open_form' => '1', 'sms_send_time' => '10:30']);
        $this->assertTrue(Setting::isEnabled(SettingKey::ForceOpenForm));
        $this->assertFalse(Setting::isEnabled(SettingKey::TickerDisabled));
        $this->assertSame('10:30', Setting::getValue(SettingKey::SmsSendTime));

        $this->post("/admin/delete-user/{$eve->id}");
        $this->assertNull($eve->fresh());
    }
}
