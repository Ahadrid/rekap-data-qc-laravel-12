<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $name = env('ADMIN_NAME', 'Default Admin');
        $email = env('ADMIN_EMAIL');
        $pass = env('ADMIN_PASSWORD');
        $role = env('ADMIN_ROLE');

        if ($email && $pass) {
            User::updateOrCreate(
                ['email' => $email], // Kunci pencarian
                [
                    'name' => $name,
                    'password' => Hash::make($pass),
                    'email_verified_at' => now(),
                    'role' => $role,
                ]
            );
            
            $this->command->info("Akun Superadmin ($email) berhasil diamankan/dibuat.");
        } else {
            $this->command->error("Gagal Seeding: ADMIN_EMAIL atau ADMIN_PASSWORD belum diatur di .env");
        }
    }
}