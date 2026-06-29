<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ETMS Notification</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f3f4f6;
            padding: 40px 0;
        }
        .email-content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }
        .email-header {
            background-color: #4f46e5;
            padding: 30px 40px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .email-body {
            padding: 40px;
            color: #374151;
            line-height: 1.6;
        }
        .email-body h2 {
            margin-top: 0;
            color: #111827;
            font-size: 20px;
            font-weight: 600;
        }
        .email-message {
            margin-bottom: 30px;
            font-size: 16px;
        }
        .ticket-details {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .ticket-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .ticket-details th {
            text-align: left;
            padding: 8px 0;
            color: #6b7280;
            font-weight: 500;
            font-size: 14px;
            width: 35%;
        }
        .ticket-details td {
            padding: 8px 0;
            color: #111827;
            font-weight: 600;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: #e0e7ff;
            color: #4338ca;
        }
        .status-badge.escalated { background-color: #fee2e2; color: #b91c1c; }
        .status-badge.resolved { background-color: #d1fae5; color: #047857; }
        .status-badge.closed { background-color: #f3f4f6; color: #374151; }
        
        .action-container {
            text-align: center;
            margin-top: 30px;
        }
        .action-button {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.2s;
        }
        .action-button:hover {
            background-color: #4338ca;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 24px 40px;
            text-align: center;
            color: #6b7280;
            font-size: 13px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-content">
            <div class="email-header">
                <h1>ETMS System Alert</h1>
            </div>
            
            <div class="email-body">
                <h2>{{ $action }}</h2>
                
                <div class="email-message">
                    {{ $notificationMessage }}
                </div>
                
                <div class="ticket-details">
                    <table>
                        <tr>
                            <th>Ticket ID</th>
                            <td>#{{ $ticket->reference_number }}</td>
                        </tr>
                        <tr>
                            <th>Title</th>
                            <td>{{ $ticket->title }}</td>
                        </tr>
                        <tr>
                            <th>Priority</th>
                            <td>{{ $ticket->priority->name ?? 'Unassigned' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @php
                                    $statusClass = in_array($ticket->status, ['escalated', 'resolved', 'closed']) ? $ticket->status : 'default';
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    {{ str_replace('_', ' ', Str::title($ticket->status)) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>{{ $ticket->updated_at->format('M d, Y h:i A') }}</td>
                        </tr>
                    </table>
                </div>
                
                <div class="action-container">
                    <a href="{{ $url }}" class="action-button">View Ticket Details</a>
                </div>
            </div>
            
            <div class="email-footer">
                <p>&copy; {{ date('Y') }} Enterprise Ticket Management System. All rights reserved.</p>
                <p>This is an automated message. Please do not reply directly to this email.</p>
            </div>
        </div>
    </div>
</body>
</html>
