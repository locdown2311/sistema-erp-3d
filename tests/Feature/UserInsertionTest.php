<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserInsertionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_a_user_can_be_inserted_into_the_database(): void
    {
        $userData = [
            'name' => 'John Doe Test',
            'email' => 'johndoe_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'store_name' => 'John Store',
            'slug' => 'john-store-' . uniqid(),
            'whatsapp' => '11999999999',
            'is_admin' => false,
        ];

        $user = User::create($userData);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'store_name' => 'John Store',
        ]);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
    }
}
