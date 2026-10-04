<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private User $alice;

    protected function setUp(): void
    {
        parent::setUp();
        OrderTest::createMenu();

        $this->alice = OrderTest::createUser('Alice');
    }

    public function test_user_logs_in_and_is_sent_to_the_intended_page(): void
    {
        $this->get('/commande')->assertRedirect(route('login'));

        $this->post('/login', ['email' => 'alice@example.com', 'password' => 'mot-de-passe-solide', 'remember' => '1'])
            ->assertRedirect(route('orders.create'));

        $this->assertAuthenticatedAs($this->alice);
        $this->assertNotNull($this->alice->fresh()->remember_token);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->post('/login', ['email' => 'alice@example.com', 'password' => 'mauvais'])
            ->assertSessionHasErrors(['email' => 'Email ou mot de passe incorrect']);

        $this->assertGuest();
    }

    public function test_imported_user_without_password_cannot_log_in(): void
    {
        User::query()->create(['name' => 'Bob', 'email' => 'bob@example.com', 'creation_date' => '2024-01-01']);

        $this->post('/login', ['email' => 'bob@example.com', 'password' => ''])->assertSessionHasErrors('password');
        $this->post('/login', ['email' => 'bob@example.com', 'password' => 'nimporte'])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_throttled_after_five_failures(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'alice@example.com', 'password' => 'mauvais']);
        }

        $this->post('/login', ['email' => 'alice@example.com', 'password' => 'mot-de-passe-solide'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_logs_out(): void
    {
        $this->actingAs($this->alice)->post('/logout')->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_forgot_password_does_not_reveal_unknown_emails(): void
    {
        Notification::fake();
        $message = "Si un compte existe pour cet email, un lien vient d'y être envoyé.";

        $this->post('/forgot-password', ['email' => 'alice@example.com'])->assertSessionHas('success', $message);
        Notification::assertSentTo($this->alice, SetPasswordNotification::class);

        $this->post('/forgot-password', ['email' => 'inconnu@example.com'])->assertSessionHas('success', $message);
        Notification::assertSentTimes(SetPasswordNotification::class, 1);
    }

    public function test_reset_link_sets_a_new_password(): void
    {
        $token = Password::broker()->createToken($this->alice);

        $this->get(route('password.reset', ['token' => $token, 'email' => 'alice@example.com']))
            ->assertOk()
            ->assertSee('alice@example.com');

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'alice@example.com',
            'password' => 'nouveau-mdp',
            'password_confirmation' => 'autre-chose',
        ])->assertSessionHasErrors(['password' => 'Les deux mots de passe ne correspondent pas']);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'alice@example.com',
            'password' => 'nouveau-mdp',
            'password_confirmation' => 'nouveau-mdp',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($this->alice);
        $this->post('/logout');
        $this->post('/login', ['email' => 'alice@example.com', 'password' => 'nouveau-mdp']);
        $this->assertAuthenticatedAs($this->alice);
    }

    public function test_invalid_reset_token_is_rejected(): void
    {
        $this->post('/reset-password', [
            'token' => 'faux-jeton',
            'email' => 'alice@example.com',
            'password' => 'nouveau-mdp',
            'password_confirmation' => 'nouveau-mdp',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
