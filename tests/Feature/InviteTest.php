<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use Illuminate\Support\Str;

class InviteTest extends TestCase
{

    use RefreshDatabase; 

    /** @test */
    public function a_user_can_be_invited()
    {
        $user = User::factory()->make();

        $validEmail = str_replace(' ', '.', strtolower($user->name)) . '@' . env('EMAIL_DOMAIN');

        $payload = [
            'name' => $user->name,
            'email' => $validEmail
        ];

        $invite = $this->post('/invite', $payload);

        $this->assertDatabaseHas('users', [
            'email' => $validEmail,
            'name' => $user->name
        ]);
    }
    
    /** @test */
    public function an_invited_user_must_have_a_valid_email()
    {
        $user = User::factory()->make();

        $payload = [
            'name' => $user->name,
            'email' => $user->email
        ];

        $invite = $this->post('/invite', $payload);

        $invite->assertStatus(422);
    }

    /** @test */
    public function an_invited_user_can_create_a_password()
    {
        // Unverified User
        $user = User::factory()->create(['email_verified_at' => null, 'password' => null, 'remember_token' => null])->toArray();

        $url = '/create-profile/' . $user['id'];
        
        $response = $this->post($url, $user);

        $newPass = Str(16);
        

        dd($url);
    }
    
    
}
