<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckSlaViolations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etms:check-sla';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for tickets nearing SLA deadlines and send warnings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for SLA deadline warnings...');

        // Find tickets at risk (within 2 hours of resolve SLA)
        $atRiskTickets = Ticket::slaAtRisk(2)->get();
        
        $warningCount = 0;

        foreach ($atRiskTickets as $ticket) {
            $cacheKey = 'sla_warning_sent_' . $ticket->id;

            // Prevent spamming the warning
            if (!Cache::has($cacheKey)) {
                $message = "Warning: Ticket #{$ticket->reference_number} is approaching its SLA deadline. Resolves at: {$ticket->sla_resolve_at->format('Y-m-d H:i')}.";
                
                // Notify assignee if exists
                if ($ticket->assignee) {
                    $ticket->assignee->notify(new TicketNotification($ticket, 'SLA Deadline Warning', $message));
                }

                // Notify Team Leads
                $teamLeads = User::byRole('team_lead')->get();
                foreach ($teamLeads as $lead) {
                    $lead->notify(new TicketNotification($ticket, 'SLA Deadline Warning', $message));
                }

                Cache::put($cacheKey, true, now()->addDays(7));
                $warningCount++;
                
                $this->line("Sent warning for ticket: {$ticket->reference_number}");
            }
        }

        $this->info("Completed. Sent {$warningCount} new SLA warnings.");
    }
}
