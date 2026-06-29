<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ── Define all permissions ─────────────────────────────────────────────
        $permissions = [
            // Ticket permissions
            ['name' => 'ticket.create',        'display_name' => 'Create Ticket',            'module' => 'ticket'],
            ['name' => 'ticket.view_own',       'display_name' => 'View Own Tickets',          'module' => 'ticket'],
            ['name' => 'ticket.view_assigned',  'display_name' => 'View Assigned Tickets',     'module' => 'ticket'],
            ['name' => 'ticket.view_team',      'display_name' => 'View Team Tickets',         'module' => 'ticket'],
            ['name' => 'ticket.view_all',       'display_name' => 'View All Tickets',          'module' => 'ticket'],
            ['name' => 'ticket.edit',           'display_name' => 'Edit Ticket',               'module' => 'ticket'],
            ['name' => 'ticket.delete',         'display_name' => 'Delete Ticket',             'module' => 'ticket'],
            ['name' => 'ticket.assign',         'display_name' => 'Assign Ticket',             'module' => 'ticket'],
            ['name' => 'ticket.accept',         'display_name' => 'Accept Assignment',         'module' => 'ticket'],
            ['name' => 'ticket.reject',         'display_name' => 'Reject Assignment',         'module' => 'ticket'],
            ['name' => 'ticket.escalate',       'display_name' => 'Escalate Ticket',           'module' => 'ticket'],
            ['name' => 'ticket.resolve',        'display_name' => 'Resolve Ticket',            'module' => 'ticket'],
            ['name' => 'ticket.close',          'display_name' => 'Close Ticket',              'module' => 'ticket'],
            ['name' => 'ticket.reopen',         'display_name' => 'Reopen Ticket',             'module' => 'ticket'],
            ['name' => 'ticket.cancel',         'display_name' => 'Cancel Ticket',             'module' => 'ticket'],
            ['name' => 'ticket.update_status',  'display_name' => 'Update Ticket Status',      'module' => 'ticket'],

            // Comment permissions
            ['name' => 'comment.create',        'display_name' => 'Add Comment',               'module' => 'comment'],
            ['name' => 'comment.internal',      'display_name' => 'Add Internal Note',         'module' => 'comment'],
            ['name' => 'comment.delete',        'display_name' => 'Delete Comment',            'module' => 'comment'],

            // Attachment permissions
            ['name' => 'attachment.upload',     'display_name' => 'Upload Attachment',         'module' => 'attachment'],
            ['name' => 'attachment.download',   'display_name' => 'Download Attachment',       'module' => 'attachment'],
            ['name' => 'attachment.delete',     'display_name' => 'Delete Attachment',         'module' => 'attachment'],

            // SLA permissions
            ['name' => 'sla.view',              'display_name' => 'View SLA Dashboard',        'module' => 'sla'],
            ['name' => 'sla.manage',            'display_name' => 'Manage SLA Policies',       'module' => 'sla'],

            // Report permissions
            ['name' => 'report.own',            'display_name' => 'Generate Own Reports',      'module' => 'report'],
            ['name' => 'report.team',           'display_name' => 'Generate Team Reports',     'module' => 'report'],
            ['name' => 'report.all',            'display_name' => 'Generate All Reports',      'module' => 'report'],

            // Admin permissions
            ['name' => 'admin.users',           'display_name' => 'Manage Users',              'module' => 'admin'],
            ['name' => 'admin.roles',           'display_name' => 'Manage Roles',              'module' => 'admin'],
            ['name' => 'admin.categories',      'display_name' => 'Manage Categories',         'module' => 'admin'],
            ['name' => 'admin.priorities',      'display_name' => 'Manage Priorities',         'module' => 'admin'],
            ['name' => 'admin.departments',     'display_name' => 'Manage Departments',        'module' => 'admin'],
            ['name' => 'admin.settings',        'display_name' => 'Manage System Settings',    'module' => 'admin'],
            ['name' => 'admin.audit_logs',      'display_name' => 'View Audit Logs',           'module' => 'admin'],

            // Knowledge Base
            ['name' => 'kb.view',               'display_name' => 'View Knowledge Base',       'module' => 'kb'],
            ['name' => 'kb.manage',             'display_name' => 'Manage Knowledge Base',     'module' => 'kb'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['name' => $perm['name']], $perm);
        }

        // ── Assign permissions to roles ───────────────────────────────────────

        $rolePermissions = [
            'user' => [
                'ticket.create', 'ticket.view_own', 'ticket.reopen', 'ticket.cancel',
                'comment.create', 'attachment.upload', 'attachment.download',
                'report.own', 'kb.view',
            ],
            'agent' => [
                'ticket.create', 'ticket.view_own', 'ticket.view_assigned',
                'ticket.accept', 'ticket.reject', 'ticket.escalate',
                'ticket.resolve', 'ticket.update_status',
                'comment.create', 'comment.internal',
                'attachment.upload', 'attachment.download', 'attachment.delete',
                'report.own', 'sla.view', 'kb.view',
            ],
            'team_lead' => [
                'ticket.create', 'ticket.view_own', 'ticket.view_assigned',
                'ticket.view_team', 'ticket.assign', 'ticket.escalate',
                'ticket.resolve', 'ticket.close', 'ticket.update_status',
                'comment.create', 'comment.internal', 'comment.delete',
                'attachment.upload', 'attachment.download', 'attachment.delete',
                'sla.view', 'report.own', 'report.team',
                'kb.view', 'kb.manage',
                'admin.audit_logs',
            ],
            'admin' => array_column($permissions, 'name'), // All permissions
        ];

        foreach ($rolePermissions as $roleName => $permNames) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $permIds = Permission::whereIn('name', $permNames)->pluck('id');
                $role->permissions()->sync($permIds);
            }
        }

        $this->command->info('Permissions seeded and assigned to roles.');
    }
}
