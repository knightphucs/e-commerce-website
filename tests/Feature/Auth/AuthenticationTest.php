<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_customers_are_redirected_to_the_shop_after_login(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('shop.index', absolute: false));
    }

    public function test_editors_are_redirected_to_the_admin_dashboard_after_login(): void
    {
        $user = User::factory()->editor()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_customers_are_returned_to_the_page_they_were_redirected_from_after_login(): void
    {
        $user = User::factory()->create();

        $this->get(route('checkout.create'));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('checkout.create', absolute: false));
    }

    public function test_editors_who_were_blocked_from_a_client_page_are_redirected_to_the_dashboard_after_login(): void
    {
        $user = User::factory()->editor()->create();

        $this->get(route('checkout.create'));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_editors_who_were_blocked_from_an_admin_page_are_returned_to_it_after_login(): void
    {
        $user = User::factory()->editor()->create();

        $this->get(route('orders.index'));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('orders.index', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_blocked_users_can_not_authenticate(): void
    {
        $user = User::factory()->create(['status' => 'blocked']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('shop.index', absolute: false));
    }

    public function test_editors_logging_out_of_the_admin_panel_are_redirected_to_login(): void
    {
        $user = User::factory()->editor()->create();

        $response = $this->actingAs($user)->post(route('admin.logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
    }
}
