<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\PasswordPolicy;
use Illuminate\Console\Command;

/** Creates or resets an admin login: php artisan admin:create you@example.com "a-strong-password" */
class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email} {password} {--name=Crystal Movers Admin}';

    protected $description = 'Create an admin user, or reset the password of an existing one';

    public function handle(): int
    {
        $password = (string) $this->argument('password');

        if ($problem = PasswordPolicy::problem($password)) {
            $this->error($problem);

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => strtolower((string) $this->argument('email'))],
            ['name' => $this->option('name'), 'password' => $password, 'role' => 'admin', 'is_active' => true],
        );

        $this->info("Admin ready: {$user->email}. You can sign in at /admin.");

        return self::SUCCESS;
    }
}
