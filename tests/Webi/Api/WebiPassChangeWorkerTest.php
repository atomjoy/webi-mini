<?php

namespace Tests\Webi\Api;

use Illuminate\Support\Facades\Auth;
use Webi\Traits\Tests\AuthenticatedTestCase;

/*
	php artisan vendor:publish --tag=webi-tests --force
	php artisan test --testsuite=Webi --stop-on-failure
*/

class WebiPassChangeWorkerTest extends AuthenticatedTestCase
{
	/** @test */
	function logged_user_data()
	{
		$res = $this->getJson('web/api/test/user');

		$res->assertStatus(200)->assertJsonStructure([
			'message', 'user'
		]);
	}

	/** @test */
	function change_logged_user_password()
	{
		$res = $this->postJson('/web/api/change-password', [
			'password_current' => 'Password1234X',
			'password' => 'Password1234#',
			'password_confirmation' => 'Password1234#'
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'Invalid current password.'
		]);

		$res = $this->postJson('/web/api/change-password', [
			'password_current' => 'password123',
			'password' => 'password',
			'password_confirmation' => 'password123'
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The password must be at least 11 characters.'
		]);

		$res = $this->postJson('/web/api/change-password', [
			'password_current' => 'password123',
			'password' => 'password1234',
			'password_confirmation' => 'password'
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The password must contain at least one uppercase and one lowercase letter.'
		]);

		$res = $this->postJson('/web/api/change-password', [
			'password_current' => 'password123',
			'password' => 'Password1234',
			'password_confirmation' => 'Password1234'
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The password must contain at least one symbol.'
		]);

		$res = $this->postJson('/web/api/change-password', [
			'password_current' => 'password123',
			'password' => 'Passwordoooo#',
			'password_confirmation' => 'Passwordoooo#'
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The password must contain at least one number.'
		]);

		$res = $this->postJson('/web/api/change-password', [
			'password_current' => 'password123',
			'password' => 'Password1234#',
			'password_confirmation' => 'Password1234#1'
		]);

		$res->assertStatus(422)->assertJson([
			'message' => 'The password confirmation does not match.'
		]);

		$res = $this->postJson('/web/api/change-password', [
			'password_current' => 'password123',
			'password' => 'Password1234#',
			'password_confirmation' => 'Password1234#'
		]);

		$res->assertStatus(200)->assertJson([
			'message' => 'Password has been updated.'
		]);

		Auth::logout();

		$res = $this->postJson('/web/api/change-password', [
			'password_current' => 'password1234#',
			'password' => 'Password12345#',
			'password_confirmation' => 'Password12345#'
		]);

		$res->assertStatus(401)->assertJson([
			'message' => 'Unauthenticated.'
		]);

		$res = $this->postJson('/web/api/login', [
			'email' => $this->user->email,
			'password' => 'Password1234#'
		]);

		$res->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		])->assertJsonStructure([
			'user'
		]);

		$this->assertNotNull($res['user']);
	}

	/** @test */
	function dont_allow_change_not_logged_user_password()
	{
		Auth::logout();

		$res = $this->postJson('/web/api/change-password', [
			'password_current' => 'password123',
			'password' => 'password1234',
			'password_confirmation' => 'password1234'
		]);

		$res->assertStatus(401)->assertJson([
			'message' => 'Unauthenticated.'
		]);
	}

	/** @test */
	function user_logout()
	{
		$res = $this->getJson('/web/api/logout');

		$res->assertStatus(200)->assertJson([
			'message' => 'Logged out.'
		]);
	}
}
