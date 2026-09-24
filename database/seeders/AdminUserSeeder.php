<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Creates a single development administrator account.
 *
 * Credentials come from environment variables and are never hardcoded:
 *
 *     ADMIN_NAME="StudyNest Admin"
 *     ADMIN_EMAIL=admin@example.com
 *     ADMIN_PASSWORD=<strong-development-password>
 *
 * Run with:  php artisan db:seed --class=AdminUserSeeder
 *
 * If ADMIN_PASSWORD is not set the seeder refuses to run instead of creating
 * an account with a predictable password.
 */
class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = (string) (env('ADMIN_EMAIL') ?: 'admin@example.com');
        $password = (string) env('ADMIN_PASSWORD');
        $name = (string) (env('ADMIN_NAME') ?: 'StudyNest Admin');

        if ($password === '') {
            throw new RuntimeException(
                'ADMIN_PASSWORD is not set. Add a strong, local-only value to your .env file '.
                    'before running the AdminUserSeeder. Never commit real credentials.'
            );
        }

        $admin = User::query()->firstOrNew(['email' => $email]);

        $admin->name = $name;
        $admin->email = $email;
        $admin->password = Hash::make($password);
        $admin->role = User::ROLE_ADMIN;
        $admin->email_verified_at = now();
        $admin->save();

        $this->command?->info("Admin account ready: {$admin->email} ({$admin->role})");
    }
}
