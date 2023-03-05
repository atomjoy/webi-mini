<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class WebiUserFactory extends Factory
{
	protected $model = User::class;

	public function definition()
	{
		return [
			'name' => $this->faker->name(),
			'email' => uniqid() . '@gmail.com',
			'username' => uniqid('user.'),
			'email_verified_at' => now(),
			'password' => Hash::make('password123'),
			'remember_token' => Str::random(50),
			'newsletter_on' => 1,
			'image' => 'https://www.w3schools.com/howto/img_avatar.png',
			'mobile_prefix' => '48',
			'mobile' => $this->faker->numerify('#########'),
			'location' => $this->faker->country(),
			'website' => 'https://web.site',
			'code' => Str::random(11),
			'ip' => '127.0.0.1',
		];
	}

	public function subscribed()
	{
		return $this->state(function (array $attributes) {
			return [
				'newsletter_on' => 1,
			];
		});
	}
}
