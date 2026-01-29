<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(4)->create();

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@admin.com',
            'password' => 'admin123',
            'role' => 'superadmin',
        ]);

        $this->call([
            MasterDataSeeder::class,
        ]);
    }
}
