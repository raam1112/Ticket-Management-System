<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Information Technology', 'code' => 'IT',      'description' => 'IT support and infrastructure'],
            ['name' => 'Human Resources',         'code' => 'HR',      'description' => 'HR policies and employee support'],
            ['name' => 'Finance & Accounts',      'code' => 'FIN',     'description' => 'Billing, payments, and accounts'],
            ['name' => 'Operations',              'code' => 'OPS',     'description' => 'Operational support'],
            ['name' => 'Sales & Marketing',       'code' => 'SALES',   'description' => 'Sales and marketing support'],
            ['name' => 'Customer Service',        'code' => 'CS',      'description' => 'Customer-facing support team'],
            ['name' => 'Engineering',             'code' => 'ENG',     'description' => 'Software and product engineering'],
            ['name' => 'Administration',          'code' => 'ADMIN',   'description' => 'General administration'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['code' => $dept['code']], $dept);
        }

        $this->command->info('Departments seeded.');
    }
}
