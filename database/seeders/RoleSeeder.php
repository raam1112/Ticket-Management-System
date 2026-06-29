<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name'         => 'user',
                'display_name' => 'Customer / User',
                'description'  => 'End user who raises support tickets',
            ],
            [
                'name'         => 'agent',
                'display_name' => 'Support Agent',
                'description'  => 'Frontline support staff handling tickets',
            ],
            [
                'name'         => 'team_lead',
                'display_name' => 'Team Lead',
                'description'  => 'Senior agent who manages team and SLA',
            ],
            [
                'name'         => 'admin',
                'display_name' => 'Administrator',
                'description'  => 'Full system access and configuration',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }

        $this->command->info('Roles seeded.');
    }
}
