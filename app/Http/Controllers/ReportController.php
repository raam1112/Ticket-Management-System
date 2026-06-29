<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Report;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $query = Report::latest();
        if (!$user->hasAnyRole(['admin', 'team_lead'])) {
            $query->where('generated_by', $user->id);
        }

        $reports = $query->paginate(20);
        return view('reports.index', compact('reports'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:150',
            'type'      => 'required|in:tickets,open_tickets,closed_tickets,sla,agents,escalations,categories,user_activity,resolution_time,monthly_stats',
            'format'    => 'required|in:pdf,csv,excel',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
        ]);

        $user = auth()->user();

        // In a real production system, this would be dispatched to a background job.
        // For this demo, we generate synchronously.
        
        $report = Report::create([
            'generated_by' => $user->id,
            'name'         => $request->name,
            'type'         => $request->type,
            'format'       => $request->format,
            'filters'      => $request->only('date_from', 'date_to', 'status', 'department_id'),
            'status'       => 'processing',
        ]);

        try {
            $filePath = $this->generateFile($report);
            $report->update([
                'file_path'    => $filePath,
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
            AuditLog::record('report_generated', $user->id, Report::class, $report->id);
            return back()->with('success', 'Report generated successfully.');
        } catch (\Exception $e) {
            $report->update(['status' => 'failed']);
            // Log error
            \Log::error("Report generation failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Report generation failed: ' . $e->getMessage());
        }
    }

    public function download(Report $report)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'team_lead']) && $report->generated_by !== $user->id) {
            abort(403);
        }

        if (!$report->file_path || !Storage::disk('local')->exists($report->file_path)) {
            abort(404, 'Report file not found.');
        }

        return Storage::disk('local')->download($report->file_path);
    }

    protected function generateFile(Report $report)
    {
        $data = $this->getReportData($report);
        $filename = 'reports/' . $report->id . '_' . \Str::slug($report->name) . '.' . ($report->format === 'excel' ? 'xlsx' : $report->format);

        if ($report->format === 'pdf') {
            // Map types that share the tickets layout
            $templateName = in_array($report->type, ['open_tickets', 'closed_tickets', 'escalations']) ? 'tickets' : $report->type;
            
            $pdf = Pdf::loadView('reports.templates.' . $templateName, ['data' => $data, 'report' => $report]);
            Storage::disk('local')->put($filename, $pdf->output());
        } elseif ($report->format === 'csv') {
            // Simplified CSV generation for demo
            $csv = fopen('php://temp', 'r+');
            
            if (count($data) > 0) {
                fputcsv($csv, array_keys((array) $data[0]));
                foreach ($data as $row) {
                    fputcsv($csv, (array) $row);
                }
            } else {
                fputcsv($csv, ['No data found']);
            }
            
            rewind($csv);
            Storage::disk('local')->put($filename, stream_get_contents($csv));
        }

        return $filename;
    }

    protected function getReportData(Report $report)
    {
        $filters = $report->filters;
        $query = Ticket::query();

        if (!empty($filters['date_from'])) $query->whereDate('created_at', '>=', $filters['date_from']);
        if (!empty($filters['date_to']))   $query->whereDate('created_at', '<=', $filters['date_to']);

        switch ($report->type) {
            case 'tickets':
                return $this->formatTickets($query->with('category', 'priority', 'assignee')->get());

            case 'open_tickets':
                return $this->formatTickets($query->open()->with('category', 'priority', 'assignee')->get());

            case 'closed_tickets':
                return $this->formatTickets($query->byStatus('closed')->with('category', 'priority', 'assignee')->get());

            case 'escalations':
                return $this->formatTickets($query->where('escalation_count', '>', 0)->with('category', 'priority', 'assignee')->get());

            case 'sla':
                return $query->with('slaPolicy')->get()->map(function($t) {
                    return [
                        'Reference'    => $t->reference_number,
                        'Title'        => $t->title,
                        'Status'       => $t->status,
                        'SLA Policy'   => $t->slaPolicy?->name ?? 'None',
                        'Resolve Due'  => $t->sla_resolve_at ? $t->sla_resolve_at->format('Y-m-d H:i') : 'N/A',
                        'Resolved At'  => $t->resolved_at ? $t->resolved_at->format('Y-m-d H:i') : 'N/A',
                        'SLA Breached' => $t->is_sla_breached ? 'Yes' : 'No',
                    ];
                })->toArray();

            case 'agents':
                $agents = User::byRole('agent')->withCount([
                    'assignedTickets as total_assigned',
                    'assignedTickets as open_count' => fn($q) => $q->whereNotIn('status', ['resolved','closed','cancelled']),
                    'assignedTickets as resolved_count' => fn($q) => $q->where('status', 'resolved'),
                ])->get();
                return $agents->map(function($a) {
                    return [
                        'Agent Name'   => $a->name,
                        'Email'        => $a->email,
                        'Total Tickets'=> $a->total_assigned,
                        'Open Tickets' => $a->open_count,
                        'Resolved'     => $a->resolved_count,
                    ];
                })->toArray();

            case 'categories':
                return TicketCategory::withCount([
                    'tickets as total',
                    'tickets as open' => fn($q) => $q->whereNotIn('status', ['resolved','closed','cancelled']),
                    'tickets as closed' => fn($q) => $q->where('status', 'closed')
                ])->get()->map(function($c) {
                    return [
                        'Category' => $c->name,
                        'Total'    => $c->total,
                        'Open'     => $c->open,
                        'Closed'   => $c->closed,
                    ];
                })->toArray();

            case 'user_activity':
                return User::withCount('createdTickets')->get()->map(function($u) {
                    return [
                        'User'    => $u->name,
                        'Role'    => $u->primary_role ?? 'User',
                        'Tickets Created' => $u->created_tickets_count,
                    ];
                })->toArray();

            case 'resolution_time':
                return $query->whereNotNull('resolved_at')->get()->map(function($t) {
                    $hours = $t->created_at && $t->resolved_at ? $t->created_at->diffInHours($t->resolved_at) : 0;
                    return [
                        'Reference' => $t->reference_number,
                        'Created'   => $t->created_at ? $t->created_at->format('Y-m-d') : 'N/A',
                        'Resolved'  => $t->resolved_at ? $t->resolved_at->format('Y-m-d') : 'N/A',
                        'Time (Hrs)'=> $hours,
                    ];
                })->toArray();

            case 'monthly_stats':
                $stats = $query->get()->groupBy(function($t) {
                    return $t->created_at ? $t->created_at->format('Y-M') : 'Unknown';
                });
                
                $result = [];
                foreach ($stats as $month => $tickets) {
                    $result[] = [
                        'Month'    => $month,
                        'Total'    => $tickets->count(),
                        'Resolved' => $tickets->where('status', 'resolved')->count(),
                        'Closed'   => $tickets->where('status', 'closed')->count(),
                    ];
                }
                return $result;
        }

        return [];
    }

    protected function formatTickets($tickets)
    {
        return $tickets->map(function($t) {
            return [
                'Reference' => $t->reference_number,
                'Title'     => $t->title,
                'Status'    => $t->status,
                'Category'  => $t->category?->name,
                'Priority'  => $t->priority?->name,
                'Assigned'  => $t->assignee?->name,
                'Created'   => $t->created_at ? $t->created_at->format('Y-m-d H:i') : 'N/A',
            ];
        })->toArray();
    }
}
