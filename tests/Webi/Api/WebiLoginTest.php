<?php

namespace Tests\Webi\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Webi\Events\WebiUserLogged;
use Webi\Listeners\WebiUserLoggedNotification;

/*
	php artisan vendor:publish --tag=webi-tests --force
	php artisan test --testsuite=Webi --stop-on-failure
*/

class WebiLoginTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function login_user_method()
	{
		$res = $this->getJson('/web/api/login');

		$res->assertStatus(400)->assertJson([
			'message' => 'Invalid api route path or request method.'
		]);
	}

	/** @test */
	function login_user_errors()
	{
		$user = User::factory()->create();

		$res = $this->postJson('/web/api/login', [
			'email' => '',
			'password' => 'password123',
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The email field is required.'
		]);

		$res = $this->postJson('/web/api/login', [
			'email' => $user->email . 'error###',
			'password' => 'password123',
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The email must be a valid email address.'
		]);

		$res = $this->postJson('/web/api/login', [
			'email' => $user->email,
			'password' => '',
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The password field is required.'
		]);

		$res = $this->postJson('/web/api/login', [
			'email' => $user->email,
			'password' => 'password',
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The password must be at least 11 characters.'
		]);
	}

	/** @test */
	function login_user_soft_deleted()
	{
		$user = User::factory()->create([
			'email' => 'xxxxxxxxxx@gmail.com',
			'password' => Hash::make('Password123#@!'),
		]);

		$res = $this->postJson('/web/api/login', [
			'email' => 'xxxxxxxxxx@gmail.com',
			'password' => 'Password123#@!',
		]);

		$res->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		]);

		$user->delete(); // Soft deleted users not allowed

		$res = $this->postJson('/web/api/login', [
			'email' => 'xxxxxxxxxx@gmail.com',
			'password' => 'Password123#@!',
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'Invalid credentials.'
		]);
	}

	/** @test */
	function login_user()
	{
		Auth::logout();

		$user = User::factory()->create([
			'password' => Hash::make('hasło1233456')
		]);

		$this->assertDatabaseHas('users', [
			'name' => $user->name,
			'email' => $user->email,
		]);

		$res = $this->postJson('/web/api/login', [
			'email' => $user->email,
			'password' => 'hasło1233456'
		]);

		$res->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		])->assertJsonStructure([
			'user'
		])->assertJsonPath('user.email', $user->email);

		$this->assertNotNull($res['user']);
	}

	/** @test */
	function login_remember_me()
	{
		$user = User::factory()->create();

		$res = $this->postJson('/web/api/login', [
			'email' => $user->email,
			'password' => 'password123',
			'remember_me' => 1,
		]);

		$res->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		]);

		$token = User::where('email', $user->email)->first()->remember_token;

		$res = $this->withCookie('webi_token', $token)->get('/web/api/logged');

		$res->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		])->assertCookie('webi_token');
	}

	/** @test */
	function login_user_events()
	{
		Event::fake();

		$user = User::factory()->create();

		$res = $this->postJson('/web/api/login', [
			'email' => $user->email,
			'password' => 'password123',
		]);

		$res->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		])->assertJsonStructure([
			'user'
		])->assertJsonPath('user.email', $user->email);;

		// Event listeners
		Event::assertListening(
			WebiUserLogged::class,
			WebiUserLoggedNotification::class,
		);
	}
}
