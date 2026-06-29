<?php

namespace Database\Seeders;

use App\Models\SlaPolicy;
use App\Models\TicketPriority;
use Illuminate\Database\Seeder;

class TicketPrioritySeeder extends Seeder
{
    public function run(): void
    {
        $priorities = [
            [
                'name'                => 'low',
                'display_name'        => 'Low',
                'color'               => '#28a745',
                'icon'                => 'fa-arrow-down',
                'sla_hours_response'  => 24,
                'sla_hours_resolve'   => 72,
                'sort_order'          => 1,
            ],
            [
                'name'                => 'medium',
                'display_name'        => 'Medium',
                'color'               => '#ffc107',
                'icon'                => 'fa-minus',
                'sla_hours_response'  => 8,
                'sla_hours_resolve'   => 24,
                'sort_order'          => 2,
            ],
            [
                'name'                => 'high',
                'display_name'        => 'High',
                'color'               => '#fd7e14',
                'icon'                => 'fa-arrow-up',
                'sla_hours_response'  => 4,
                'sla_hours_resolve'   => 8,
                'sort_order'          => 3,
            ],
            [
                'name'                => 'critical',
                'display_name'        => 'Critical',
                'color'               => '#dc3545',
                'icon'                => 'fa-exclamation-triangle',
                'sla_hours_response'  => 1,
                'sla_hours_resolve'   => 4,
                'sort_order'          => 4,
            ],
        ];

        foreach ($priorities as $priority) {
            TicketPriority::updateOrCreate(['name' => $priority['name']], array_merge($priority, ['is_active' => true]));
        }

        // Create default SLA policies (global, not category-specific)
        $createdPriorities = TicketPriority::all()->keyBy('name');

        $slaPolicies = [
            [
                'name'                    => 'Low Priority SLA',
                'priority_id'             => $createdPriorities['low']->id,
                'response_time_hours'     => 24,
                'resolution_time_hours'   => 72,
                'escalation_after_hours'  => 48,
                'business_hours_only'     => false,
            ],
            [
                'name'                    => 'Medium Priority SLA',
                'priority_id'             => $createdPriorities['medium']->id,
                'response_time_hours'     => 8,
                'resolution_time_hours'   => 24,
                'escalation_after_hours'  => 16,
                'business_hours_only'     => false,
            ],
            [
                'name'                    => 'High Priority SLA',
                'priority_id'             => $createdPriorities['high']->id,
                'response_time_hours'     => 4,
                'resolution_time_hours'   => 8,
                'escalation_after_hours'  => 6,
                'business_hours_only'     => false,
            ],
            [
                'name'                    => 'Critical Priority SLA',
                'priority_id'             => $createdPriorities['critical']->id,
                'response_time_hours'     => 1,
                'resolution_time_hours'   => 4,
                'escalation_after_hours'  => 2,
                'business_hours_only'     => false,
            ],
        ];

        foreach ($slaPolicies as $policy) {
            SlaPolicy::updateOrCreate(
                ['name' => $policy['name']],
                array_merge($policy, ['is_active' => true])
            );
        }

        $this->command->info('Ticket priorities and default SLA policies seeded.');
    }
}
