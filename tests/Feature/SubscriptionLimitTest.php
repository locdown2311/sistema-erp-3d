<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_plan_blocks_product_creation_after_10_products()
    {
        // Setup: Run plan seeder to populate Free, Basic, Pro plans
        $this->seed(PlanSeeder::class);

        // Fetch the free plan
        $freePlan = Plan::where('slug', 'free')->first();
        $this->assertNotNull($freePlan, 'Free plan should exist in the database');

        // Create the user requested by the test owner
        $user = User::factory()->create([
            'email' => 'igor2396@outlook.com',
        ]);

        // Assign the free plan to the user
        $user->subscriptions()->create([
            'plan_id' => $freePlan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);

        // Verify the user is on the free plan and their product limit
        $this->assertEquals(10, $user->currentPlan()->max_products);

        // Create 10 products directly in the database
        for ($i = 1; $i <= 10; $i++) {
            Product::create([
                'user_id' => $user->id,
                'name' => 'Produto de Teste ' . $i,
                'base_price' => 10.00,
                'base_cost' => 5.00,
            ]);
        }

        // Assert 10 products exist
        $this->assertEquals(10, $user->products()->count(), 'User should have exactly 10 products.');

        // Login the user to make HTTP requests
        $this->actingAs($user);

        // Attempt to create the 11th product via HTTP Request
        $response = $this->post(route('products.store'), [
            'name' => 'Produto Proibido 11',
            'base_price' => 15.00,
            'base_cost' => 8.00,
        ]);

        // Should return a redirection to plans.index
        $response->assertRedirect(route('plans.index'));
        
        // Assert the session has the error message
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Limite de produtos atingido', session('error'));

        // Assert that the 11th product was NOT created in the database
        $this->assertEquals(10, Product::where('user_id', $user->id)->count());
    }
}
