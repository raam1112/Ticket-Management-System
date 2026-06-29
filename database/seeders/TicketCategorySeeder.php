<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use Illuminate\Database\Seeder;

class TicketCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Technical Support',  'slug' => 'technical-support',  'description' => 'Help with software and application issues', 'icon' => 'fa-headset',           'color' => '#6f42c1', 'sort_order' => 1],
            ['name' => 'Software Bug',       'slug' => 'software-bug',       'description' => 'Report errors, crashes, and application bugs', 'icon' => 'fa-bug',               'color' => '#dc3545', 'sort_order' => 2],
            ['name' => 'Hardware Issue',     'slug' => 'hardware-issue',     'description' => 'Problems with physical devices and equipment', 'icon' => 'fa-server',            'color' => '#fd7e14', 'sort_order' => 3],
            ['name' => 'Feature Request',    'slug' => 'feature-request',    'description' => 'Suggest a new feature or improvement', 'icon' => 'fa-lightbulb',         'color' => '#28a745', 'sort_order' => 4],
            ['name' => 'Account Management', 'slug' => 'account-management', 'description' => 'Issues with logins, roles, and access', 'icon' => 'fa-user-cog',          'color' => '#17a2b8', 'sort_order' => 5],
            ['name' => 'Payment Issue',      'slug' => 'payment-issue',      'description' => 'Billing, invoices, and payment failures', 'icon' => 'fa-credit-card',       'color' => '#e83e8c', 'sort_order' => 6],
            ['name' => 'Server Issue',       'slug' => 'server-issue',       'description' => 'Hosting, downtime, and backend infrastructure', 'icon' => 'fa-database',          'color' => '#6c757d', 'sort_order' => 7],
            ['name' => 'Security Issue',     'slug' => 'security-issue',     'description' => 'Report vulnerabilities or security concerns', 'icon' => 'fa-shield-alt',        'color' => '#343a40', 'sort_order' => 8],
            ['name' => 'Customer Complaint', 'slug' => 'customer-complaint', 'description' => 'General feedback or service complaints', 'icon' => 'fa-exclamation-circle','color' => '#ffc107', 'sort_order' => 9],
            ['name' => 'Other Requests',     'slug' => 'other-requests',     'description' => 'Any other general inquiries or requests', 'icon' => 'fa-question-circle',   'color' => '#007bff', 'sort_order' => 10],
        ];

        foreach ($categories as $cat) {
            TicketCategory::updateOrCreate(['slug' => $cat['slug']], array_merge($cat, ['is_active' => true]));
        }

        $this->command->info('Ticket categories seeded.');
    }
}
