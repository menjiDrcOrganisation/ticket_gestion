<?php

namespace Tests\Feature\Auth;

use App\Mail\EnvoiMotDePasseOublieMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    private function demanderLien(User $user): EnvoiMotDePasseOublieMail
    {
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');

        $envoye = null;
        Mail::assertSent(EnvoiMotDePasseOublieMail::class, function (EnvoiMotDePasseOublieMail $mail) use ($user, &$envoye) {
            $envoye = $mail;

            return $mail->hasTo($user->email);
        });

        return $envoye;
    }

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        $user = User::factory()->create();

        $mail = $this->demanderLien($user);

        $this->assertStringContainsString($mail->token, $mail->resetUrl);
    }

    public function test_reset_password_link_is_refused_for_unknown_email(): void
    {
        $this->post('/forgot-password', ['email' => 'inconnu@example.com'])
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $mail = $this->demanderLien($user);

        $this->get('/reset-password/'.$mail->token)->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $mail = $this->demanderLien($user);

        $this->post('/reset-password', [
            'token' => $mail->token,
            'email' => $user->email,
            'password' => 'NouveauMotDePasse1',
            'password_confirmation' => 'NouveauMotDePasse1',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $user->refresh();
        $this->assertTrue(Hash::check('NouveauMotDePasse1', $user->password));
        $this->assertFalse($user->must_change_password);
    }
}
