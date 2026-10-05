<?php

namespace Tests\Feature;

use App\Enums\SettingKey;
use App\Models\Announcement;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\SetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        OrderTest::createMenu();

        $this->admin = OrderTest::createUser('Robin', isAdmin: true);
    }

    public function test_admin_pages_require_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->post('/admin/create-user', ['name' => 'Eve'])->assertRedirect(route('login'));

        $this->assertNull(User::query()->where('name', 'Eve')->first());
    }

    public function test_admin_is_sent_back_to_admin_after_login(): void
    {
        $this->get('/admin');

        $this->post('/login', ['email' => 'robin@example.com', 'password' => 'mot-de-passe-solide'])
            ->assertRedirect(route('admin.index'));
        $this->get('/admin')->assertOk()->assertSee('Administration');
    }

    public function test_non_admin_is_forbidden(): void
    {
        $this->actingAs(OrderTest::createUser('Alice'))->get('/admin')->assertForbidden();

        $this->admin->update(['is_admin' => false]);
        $this->actingAs($this->admin)->get('/admin')->assertForbidden();
    }

    public function test_admin_manages_users_announcements_and_settings(): void
    {
        $this->actingAs($this->admin);

        $this->post('/admin/create-user', ['name' => 'Eve', 'email' => ' Eve@Example.com '])->assertSessionHas('success');
        $eve = User::query()->where('name', 'Eve')->sole();
        $this->assertSame('eve@example.com', $eve->email);
        $this->assertFalse($eve->hasActivatedAccount());

        $this->post("/admin/edit-user/{$eve->id}", ['name' => 'Ève', 'email' => ''])->assertSessionHas('success');
        $this->assertSame('Ève', $eve->fresh()->name);
        $this->assertNull($eve->fresh()->email);

        $this->post("/admin/set-role/{$eve->id}")->assertSessionHas('success');
        $this->assertTrue($eve->fresh()->is_admin);

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

    public function test_user_form_is_validated(): void
    {
        $this->actingAs($this->admin);

        $this->assertFlashedError($this->post('/admin/create-user', ['name' => '  ']), "Il faut un nom pour l'utilisateur");
        $this->assertFlashedError($this->post('/admin/create-user', ['name' => 'Eve', 'email' => 'pas-un-email']), "L'email pas-un-email n'est pas valide");
        $this->assertFlashedError($this->post('/admin/create-user', ['name' => 'Eve', 'email' => 'robin@example.com']), "L'email robin@example.com est déjà utilisé");

        $this->assertNull(User::query()->where('name', 'Eve')->first());

        // Garder son propre email n'est pas un doublon
        $this->post("/admin/edit-user/{$this->admin->id}", ['name' => 'Robin P.', 'email' => 'robin@example.com'])
            ->assertSessionHas('success');
    }

    public function test_admin_invites_an_imported_user(): void
    {
        Notification::fake();
        $imported = User::query()->create(['name' => 'Alice', 'creation_date' => '2024-01-01']);
        $this->actingAs($this->admin);

        $this->assertFlashedError($this->post("/admin/invite-user/{$imported->id}"), "Renseigne d'abord l'email de Alice");
        Notification::assertNothingSent();

        $imported->update(['email' => 'alice@example.com']);
        $response = $this->post("/admin/invite-user/{$imported->id}")
            ->assertSessionHas('success', 'Invitation envoyée à alice@example.com');

        $invitationLink = $response->getSession()->get('invitationLink');
        Notification::assertSentTo($imported, SetPasswordNotification::class,
            fn (SetPasswordNotification $notification) => $notification->toMail($imported)->actionUrl === $invitationLink,
        );

        // L'utilisateur choisit son mot de passe depuis le lien et se retrouve connecté
        $this->post('/logout');
        $token = basename(parse_url($invitationLink, PHP_URL_PATH));
        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'alice@example.com',
            'password' => 'mon-nouveau-mdp',
            'password_confirmation' => 'mon-nouveau-mdp',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($imported);
        $this->assertTrue($imported->fresh()->hasActivatedAccount());
    }
}
