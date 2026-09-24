<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Safe to run repeatedly: every record is resolved with updateOrCreate on
     * its natural key, so no duplicates are created on a second run. Nothing
     * is ever truncated or dropped.
     */
    public function run(): void
    {
        // Development sample data for the academic hierarchy.
        $this->call(AcademicSeeder::class);

        $this->seedDevelopmentStudent();
    }

    /**
     * Create the local development student account.
     *
     * The password is only written when the account is first created, so
     * re-seeding never silently resets a password changed during testing.
     * Never use these credentials outside local development.
     */
    protected function seedDevelopmentStudent(): void
    {
        $email = 'test@example.com';

        if (User::query()->where('email', $email)->exists()) {
            return;
        }

        User::query()->create([
            'name' => 'Test User',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => User::ROLE_STUDENT,
        ]);
    }
}
