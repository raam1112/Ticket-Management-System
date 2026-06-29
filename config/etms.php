<?php

/**
 * ETMS — Enterprise Ticket Management System
 * Custom application configuration.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Application Identity
    |--------------------------------------------------------------------------
    */
    'name'    => env('ETMS_APP_NAME', 'Enterprise Ticket Management System'),
    'version' => '1.0.0',
    'prefix'  => 'TKT',   // Ticket reference prefix

    /*
    |--------------------------------------------------------------------------
    | Ticket Settings
    |--------------------------------------------------------------------------
    */
    'tickets' => [
        'per_page'          => env('ETMS_TICKETS_PER_PAGE', 20),
        'reopen_window_days'=> env('ETMS_REOPEN_DAYS', 7),       // Days after resolution user can reopen
        'auto_close_days'   => env('ETMS_AUTO_CLOSE_DAYS', 7),   // Days after resolution to auto-close
        'assignment_mode'   => env('ETMS_ASSIGN_MODE', 'manual'), // manual | auto
    ],

    /*
    |--------------------------------------------------------------------------
    | SLA Settings
    |--------------------------------------------------------------------------
    */
    'sla' => [
        'check_interval_minutes' => 15,   // How often the SLA job runs
        'at_risk_hours'          => 2,    // Warn this many hours before SLA breach
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'max_size_mb'   => env('ETMS_MAX_UPLOAD_MB', 10),
        'max_files'     => 5,
        'allowed_mimes' => [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip', 'text/plain',
        ],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'txt'],
        'disk'  => env('ETMS_UPLOAD_DISK', 'local'),
        'path'  => 'tickets',   // Relative to storage/app/
    ],

    /*
    |--------------------------------------------------------------------------
    | Ticket Status Transitions (allowed "from" → "to" transitions)
    |--------------------------------------------------------------------------
    | Defines which status changes are valid.
    */
    'status_transitions' => [
        'open'         => ['assigned', 'cancelled'],
        'assigned'     => ['in_progress', 'open', 'cancelled'],       // open = rejected
        'in_progress'  => ['pending_user', 'escalated', 'under_review', 'resolved', 'cancelled'],
        'pending_user' => ['in_progress', 'closed'],
        'escalated'    => ['in_progress', 'assigned'],
        'under_review' => ['resolved', 'in_progress'],
        'resolved'     => ['closed', 'reopened'],
        'closed'       => [],
        'reopened'     => ['assigned'],
        'cancelled'    => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Display Colors (Bootstrap badge classes)
    |--------------------------------------------------------------------------
    */
    'role_colors' => [
        'admin'     => 'danger',
        'team_lead' => 'warning',
        'agent'     => 'info',
        'user'      => 'primary',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'tickets'    => 20,
        'comments'   => 50,
        'users'      => 25,
        'audit_logs' => 50,
        'reports'    => 20,
    ],
];
