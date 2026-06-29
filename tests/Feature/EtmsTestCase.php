<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Base test case for ETMS role-based tests.
 * Seeds roles, departments, categories, priorities, then creates
 * typed users (admin / agent / user) via helper methods.
 */
abstract class EtmsTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    // ── Seed required lookup data ─────────────────────────────────────────────

    protected function seedBaseData(): void
    {
        // Roles
        foreach (['user', 'agent', 'team_lead', 'admin'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName], [
                'display_name' => ucfirst($roleName),
                'description'  => "{$roleName} role",
            ]);
        }

        // Department (needed for user creation)
        Department::firstOrCreate(['code' => 'IT'], [
            'name'        => 'IT Department',
            'description' => 'Information Technology',
            'is_active'   => true,
        ]);

        // Categories & Priorities
        TicketCategory::firstOrCreate(['slug' => 'general'], [
            'name'       => 'General Support',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        TicketPriority::firstOrCreate(['name' => 'medium'], [
            'display_name'       => 'Medium',
            'color'              => '#f59e0b',
            'icon'               => 'fa-circle',
            'sla_hours_response' => 4,
            'sla_hours_resolve'  => 24,
            'sort_order'         => 2,
            'is_active'          => true,
        ]);
    }

    // ── User factory helpers ──────────────────────────────────────────────────

    protected function createUser(string $roleName = 'user'): User
    {
        $dept = Department::first();
        $user = User::factory()->create([
            'department_id' => $dept?->id,
            'is_active'     => true,
        ]);
        $role = Role::where('name', $roleName)->first();
        $user->roles()->attach($role->id, ['assigned_by' => $user->id]);
        return $user->fresh();
    }

    protected function createAdmin(): User    { return $this->createUser('admin'); }
    protected function createAgent(): User    { return $this->createUser('agent'); }
    protected function createCustomer(): User { return $this->createUser('user'); }

    // ── Ticket helper ─────────────────────────────────────────────────────────

    protected function createTicket(User $owner, array $overrides = []): Ticket
    {
        $category = TicketCategory::first();
        $priority = TicketPriority::first();

        return Ticket::factory()->create(array_merge([
            'created_by'  => $owner->id,
            'category_id' => $category->id,
            'priority_id' => $priority->id,
            'status'      => 'open',
            'title'       => 'Sample Test Ticket Title',
        ], $overrides));
    }
}
