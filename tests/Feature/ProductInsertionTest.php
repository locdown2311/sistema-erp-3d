<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;

class ProductInsertionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_a_product_can_be_inserted_and_associated_with_a_user(): void
    {
        // 1. Create a parent user first
        $user = User::factory()->create();

        // 2. Prepare Product Data
        $productData = [
            'user_id' => $user->id,
            'name' => 'Vaso Decorativo 3D',
            'base_price' => 45.90,
            'print_time_hours' => 2,
            'print_time_minutes' => 30,
            'weight_grams' => 150,
            'image_path' => null,
            'category' => 'Decoração',
        ];

        // 3. Insert Product
        $product = Product::create($productData);

        // 4. Assertions
        $this->assertDatabaseHas('products', [
            'user_id' => $user->id,
            'name' => 'Vaso Decorativo 3D',
            'base_price' => 45.90,
        ]);
        
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals($user->id, $product->user->id);
    }
}
