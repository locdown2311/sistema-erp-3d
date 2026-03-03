<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Entrar'); // Check for some text usually present in login
    }

    public function test_existing_user_can_login_and_redirects_to_dashboard(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        // Act
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        // Assert
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_authenticated_user_can_logout_safely(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $this->assertAuthenticatedAs($user);

        $response = $this->post('/logout');
        
        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
