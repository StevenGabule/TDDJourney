<?php

namespace Tests\Feature;

use App\Recipe;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RecipeTest extends TestCase
{
    use RefreshDatabase;
    protected $user;
    protected $recipe;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name' => 'test',
            'email' => 'test@gmail.com',
            'password' => Hash::make('secret1234')
        ]);
        $this->recipe = ['title' => 'Jollof Rice', 'procedure' => 'Parboil rice, get pepper and mix, and some spice and serve!'];
    }

    //Create user and authenticate the user
    protected function authenticate(): string
    {
        return JWTAuth::fromUser($this->user);
    }

    public function testCreate(): void
    {
        $token = $this->authenticate();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('POST', route('recipe.create'), $this->recipe);
        $response->assertStatus(200);
        $this->assertEquals(1, User::where('email', 'test@gmail.com')->first()->recipes()->count());
    }

    public function test_All(): void
    {
        $token = $this->authenticate();
        $recipe = Recipe::create($this->recipe);
        $this->user->recipes()->save($recipe);

        //call route and assert response
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('GET', route('recipe.all'));
        $response->assertStatus(200);

        //Assert the count is 1 and the title of the first item correlates
        $this->assertCount(1, $response->json());
        $this->assertEquals('Jollof Rice', $response->json()[0]['title']);
    }

    // Test the update route
    public function test_update(): void
    {
        $token = $this->authenticate();
        $recipe = Recipe::create($this->recipe);
        $this->user->recipes()->save($recipe);

        //call route and assert response
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->json('POST',
            route('recipe.update', [
                'recipe' => $recipe->id]), [
            'title' => 'Rice',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Rice', $this->user->recipes()->first()->title);
    }

    public function test_show(): void
    {
        $token = $this->authenticate();
        $recipe = Recipe::create($this->recipe);
        $this->user->recipes()->save($recipe);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->json('GET', route('recipe.show', ['recipe' => $recipe->id]));

        $response->assertStatus(200);
        $this->assertEquals('Jollof Rice', $response->json()['title']);
    }

    // Test the delete route
    public function test_delete(): void
    {
        $token = $this->authenticate();

        $recipe = Recipe::create($this->recipe);
        $this->user->recipes()->save($recipe);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->json('POST', route('recipe.delete', ['recipe' => $recipe->id]));

        $response->assertStatus(200);
        $this->assertEquals(0, $this->user->recipes()->count());
    }
}
