<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\User;
use App\Models\Wishlist;

class WishlistFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_user_can_access_wishlist_index()
    {
        $user = User::factory()->create();
        
        // Arrange: Make a mock wishlist
        Wishlist::create([
            'user_id' => $user->id,
            'url' => 'http://test.com',
            'title' => 'Produto de Teste 1',
            'price' => 19.99,
        ]);

        // Act
        $response = $this->actingAs($user)->get('/wishlists');

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Produto de Teste 1');
    }

    public function test_store_extracts_shopee_product_details_from_mock()
    {
        $user = User::factory()->create();

        // Arrange: Fake the HTTP call
        Http::fake([
            '*' => Http::response(
                '<html>
                    <meta property="og:title" content="Novo Sapato | Shopee Brasil">
                    <meta property="og:image" content="http://image.shopee/sapato.jpg">
                    <div>R$ 1.999,99</div>
                 </html>',
                200
            ) // Mocking Shopee's HTML
        ]);

        $testUrl = 'https://shopee.com.br/product-example';

        // Act
        $response = $this->actingAs($user)->post('/wishlists', [
            'url' => $testUrl,
        ]);

        // Assert
        $response->assertRedirect('/wishlists');
        
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'url' => $testUrl,
            'title' => 'Novo Sapato', // Our scraper removes " | Shopee Brasil"
            'price' => 1999.99,
            'image_url' => 'http://image.shopee/sapato.jpg'
        ]);
    }

    public function test_user_can_delete_own_wishlist_but_not_others()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $wishlist1 = Wishlist::create([
            'user_id' => $user1->id,
            'url' => 'http://test1.com',
            'title' => 'My Item',
        ]);

        // User2 attempting to delete User1's wishlist
        $responseFail = $this->actingAs($user2)->delete("/wishlists/{$wishlist1->id}");
        $responseFail->assertStatus(403);

        // User1 attempting to delete their own
        $responseSuccess = $this->actingAs($user1)->delete("/wishlists/{$wishlist1->id}");
        $responseSuccess->assertRedirect('/wishlists');

        $this->assertDatabaseMissing('wishlists', [
            'id' => $wishlist1->id
        ]);
    }

    public function test_refresh_all_updates_price_and_sets_previous_price()
    {
        $user = User::factory()->create();

        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'url' => 'https://shopee.com.br/test-item',
            'title' => 'Old Title',
            'price' => 100.00, // Old price
            'previous_price' => null,
        ]);

        // Fake the HTTP response for the refresh action returning a new price (80.00)
        Http::fake([
            '*' => Http::response(
                '<html>
                    <meta property="og:title" content="Updated Title">
                    <meta property="og:image" content="http://image.shopee/new.jpg">
                    <div>R$ 80,00</div>
                 </html>',
                200
            )
        ]);

        // Act
        $response = $this->actingAs($user)->post('/wishlists/refresh-all');

        // Assert
        $response->assertRedirect('/wishlists');
        
        // Assert DB was updated
        $this->assertDatabaseHas('wishlists', [
            'id' => $wishlist->id,
            'title' => 'Updated Title',
            'price' => 80.00,
            'previous_price' => 100.00 // It successfully tracked the history
        ]);
    }
}
