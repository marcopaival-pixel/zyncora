<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RBACSeeder::class,
            PlanSeeder::class,
            DemoUsersSeeder::class,
        ]);

        $this->command?->call('rbac:sync-users');
    }
}
