<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_register()
    {
        // users data
        $data = [
            'email' => 'test@gmail.com',
            'name' => 'Test',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ];

        //Send post request
        $res = $this->json('POST', route('api.register'), $data);
        //Assert it was successful
        $res->assertStatus(200);

        //Assert we received a token
        $this->assertArrayHasKey('token', $res->json());

        //Delete data
        User::where('email', 'test@gmail.com')->delete();
    }

    /** @test */
    public function test_login()
    {
        //Create user
        User::create([
            'name' => 'test',
            'email' => 'test@gmail.com',
            'password' => bcrypt('secret1234')
        ]);
        //attempt login
        $response = $this->json('POST', route('api.authenticate'), [
            'email' => 'test@gmail.com',
            'password' => 'secret1234',
        ]);
        //Assert it was successful and a token was received
        $response->assertStatus(200);
        $this->assertArrayHasKey('token', $response->json());
        //Delete the user
        User::where('email', 'test@gmail.com')->delete();
    }
}
