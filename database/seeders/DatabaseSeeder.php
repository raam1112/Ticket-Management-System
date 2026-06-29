<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Run in dependency order: Departments → Roles → Permissions → Categories → Priorities → Users
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            TicketCategorySeeder::class,
            TicketPrioritySeeder::class,
            AdminUserSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ ETMS database seeded successfully!');
    }
}
