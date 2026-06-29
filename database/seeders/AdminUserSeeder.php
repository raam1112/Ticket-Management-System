<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminDept = Department::where('code', 'ADMIN')->first();

        // ── Create Admin User ─────────────────────────────────────────────────
        $admin = User::updateOrCreate(
            ['email' => 'admin@etms.com'],
            [
                'name'          => 'System Administrator',
                'password'      => Hash::make('Admin@1234'),
                'department_id' => $adminDept?->id,
                'phone'         => '+91 90000 00001',
                'is_active'     => true,
                'email_verified_at' => now(),
            ]
        );
        $adminRole = Role::where('name', 'admin')->first();
        $admin->roles()->syncWithoutDetaching([$adminRole->id => ['assigned_by' => $admin->id]]);

        // ── Create Team Lead ──────────────────────────────────────────────────
        $itDept = Department::where('code', 'IT')->first();

        $teamLead = User::updateOrCreate(
            ['email' => 'teamlead@etms.com'],
            [
                'name'          => 'Sarah Connor',
                'password'      => Hash::make('Lead@1234'),
                'department_id' => $itDept?->id,
                'phone'         => '+91 90000 00002',
                'is_active'     => true,
                'email_verified_at' => now(),
            ]
        );
        $tlRole = Role::where('name', 'team_lead')->first();
        $teamLead->roles()->syncWithoutDetaching([$tlRole->id => ['assigned_by' => $admin->id]]);

        // ── Create Demo Agents ────────────────────────────────────────────────
        $agentRole = Role::where('name', 'agent')->first();

        $agents = [
            ['name' => 'Alice Smith',   'email' => 'alice@etms.com',   'password' => 'Agent@1234'],
            ['name' => 'Bob Johnson',   'email' => 'bob@etms.com',     'password' => 'Agent@1234'],
            ['name' => 'Charlie Brown', 'email' => 'charlie@etms.com', 'password' => 'Agent@1234'],
        ];

        foreach ($agents as $agentData) {
            $agent = User::updateOrCreate(
                ['email' => $agentData['email']],
                [
                    'name'              => $agentData['name'],
                    'password'          => Hash::make($agentData['password']),
                    'department_id'     => $itDept?->id,
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]
            );
            $agent->roles()->syncWithoutDetaching([$agentRole->id => ['assigned_by' => $admin->id]]);
        }

        // ── Create Demo User ──────────────────────────────────────────────────
        $csDept   = Department::where('code', 'CS')->first();
        $userRole = Role::where('name', 'user')->first();

        $demoUser = User::updateOrCreate(
            ['email' => 'user@etms.com'],
            [
                'name'              => 'John Doe',
                'password'          => Hash::make('User@1234'),
                'department_id'     => $csDept?->id,
                'phone'             => '+91 90000 00010',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );
        $demoUser->roles()->syncWithoutDetaching([$userRole->id => ['assigned_by' => $admin->id]]);

        // ── Seed default system settings ─────────────────────────────────────
        $settings = [
            // General
            ['key_name' => 'app_name',               'value' => 'Enterprise Ticket Management System', 'type' => 'string',  'group_name' => 'general',  'label' => 'Application Name', 'description' => 'The name displayed on the login screen and in the site header.'],
            ['key_name' => 'app_logo',                'value' => null,                                  'type' => 'string',  'group_name' => 'general',  'label' => 'Application Logo URL', 'description' => 'Optional URL for your company logo to display in the header.'],
            ['key_name' => 'allow_user_registration', 'value' => '0',                                   'type' => 'boolean', 'group_name' => 'general',  'label' => 'Allow User Registration', 'description' => 'If enabled, new users can create accounts from the login page.'],
            ['key_name' => 'maintenance_mode',        'value' => '0',                                   'type' => 'boolean', 'group_name' => 'general',  'label' => 'Maintenance Mode', 'description' => 'If enabled, only administrators will be able to access the system.'],
            ['key_name' => 'timezone',                'value' => 'Asia/Kolkata',                        'type' => 'string',  'group_name' => 'general',  'label' => 'System Timezone', 'description' => 'Default timezone for all dates and times displayed in the system.'],

            // Tickets
            ['key_name' => 'tickets_per_page',        'value' => '20',                                  'type' => 'integer', 'group_name' => 'tickets',  'label' => 'Tickets Per Page', 'description' => 'Number of tickets displayed per page on dashboards and lists.'],
            ['key_name' => 'reopen_window_days',      'value' => '7',                                   'type' => 'integer', 'group_name' => 'tickets',  'label' => 'Ticket Reopen Window (Days)', 'description' => 'Number of days a user is allowed to reopen a resolved ticket before it locks.'],
            ['key_name' => 'auto_close_days',         'value' => '7',                                   'type' => 'integer', 'group_name' => 'tickets',  'label' => 'Auto-close Resolved Tickets', 'description' => 'Number of days until a resolved ticket is automatically closed by the system.'],
            ['key_name' => 'assignment_mode',         'value' => 'manual',                              'type' => 'string',  'group_name' => 'tickets',  'label' => 'Assignment Mode', 'description' => 'Determines whether tickets are assigned manually or automatically to available agents (manual | auto).'],
            ['key_name' => 'allow_ticket_rating',     'value' => '1',                                   'type' => 'boolean', 'group_name' => 'tickets',  'label' => 'Allow Ticket Rating', 'description' => 'Allow users to rate the support they received after a ticket is resolved.'],

            // Mail
            ['key_name' => 'email_notifications',     'value' => '1',                                   'type' => 'boolean', 'group_name' => 'mail',     'label' => 'Enable Email Notifications', 'description' => 'Globally enable or disable all outgoing email notifications.'],
            ['key_name' => 'smtp_host',               'value' => 'smtp.mailtrap.io',                    'type' => 'string',  'group_name' => 'mail',     'label' => 'SMTP Host', 'description' => 'The hostname of your SMTP mail server.'],
            ['key_name' => 'smtp_port',               'value' => '2525',                                'type' => 'integer', 'group_name' => 'mail',     'label' => 'SMTP Port', 'description' => 'The port used to connect to your SMTP mail server.'],
            
            // Files
            ['key_name' => 'max_attachment_size_mb',  'value' => '10',                                  'type' => 'integer', 'group_name' => 'files',    'label' => 'Max Attachment Size (MB)', 'description' => 'Maximum allowed file size for ticket attachments in megabytes.'],
            ['key_name' => 'allowed_file_types',      'value' => 'jpg,png,pdf,doc,docx,zip',            'type' => 'string',  'group_name' => 'files',    'label' => 'Allowed File Types', 'description' => 'Comma-separated list of file extensions users are allowed to upload.'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key_name' => $setting['key_name']], $setting);
        }

        $this->command->info('Admin user, demo users, and system settings seeded.');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Administrator', 'admin@etms.com',    'Admin@1234'],
                ['Team Lead',     'teamlead@etms.com', 'Lead@1234'],
                ['Agent',         'alice@etms.com',    'Agent@1234'],
                ['User',          'user@etms.com',     'User@1234'],
            ]
        );
    }
}
