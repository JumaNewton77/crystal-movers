<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\PasswordPolicy;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('crystalmovers.admin.email');
        $password = config('crystalmovers.admin.password');

        // No default credentials, ever: the admin account exists only when
        // ADMIN_EMAIL and ADMIN_PASSWORD are set in .env.
        if (! $email || ! $password) {
            $this->command?->warn('Admin user skipped: set ADMIN_EMAIL and ADMIN_PASSWORD in .env, then run `php artisan db:seed --class=AdminUserSeeder`.');

            return;
        }

        if ($problem = PasswordPolicy::problem((string) $password)) {
            $this->command?->warn("Admin user skipped: {$problem}");

            return;
        }

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => config('crystalmovers.admin.name'),
                'password' => $password, // hashed by the model cast
                'role' => 'admin',
                'is_active' => true,
            ],
        );
    }
}
