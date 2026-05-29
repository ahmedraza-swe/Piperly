<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PlatformOwnerSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('platform.owner_email');
        $password = config('platform.owner_password');
        $name = config('platform.owner_name');

        $user = User::query()->firstOrNew(['email' => $email]);

        $user->fill([
            'name' => $name,
            'public_name' => $name,
            'password' => Hash::make($password),
            'is_admin' => true,
            'is_blocked' => false,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        $user->save();

        $adminRole = Role::findOrCreate('admin');
        if (! $user->hasRole($adminRole)) {
            $user->assignRole($adminRole);
        }

        $this->command?->info("Platform owner ready: {$email}");
        $this->command?->warn('Change PLATFORM_OWNER_PASSWORD in .env after first login.');
    }
}
