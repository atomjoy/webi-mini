<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \App\Models\User;
use Webi\Enums\User\UserRole;

class WebiUserSeeder extends Seeder
{
	public function run()
	{
		User::factory()->create([
			'email' => 'user@localhost',
			'newsletter_on' => 1
		]);

		User::factory()->create([
			'email' => 'worker@localhost',
			'newsletter_on' => 0
		]);

		User::factory()->create([
			'email' => 'admin@localhost',
			'newsletter_on' => 1
		]);
	}
}
