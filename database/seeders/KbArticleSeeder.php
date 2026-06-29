<?php

namespace Database\Seeders;

use App\Models\KnowledgeBaseArticle;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KbArticleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@etms.com')->first();
        if (!$admin) {
            $admin = User::first();
        }

        $articles = [
            // How to use the system
            ['category_id' => 1, 'title' => 'How to Create a New Ticket', 'content' => 'To create a ticket, navigate to the Dashboard and click the "Create Ticket" button. Fill in the title, select an appropriate category and priority, and provide a detailed description of your issue. Click Submit when done.', 'tags' => ['create', 'ticket', 'usage']],
            ['category_id' => 1, 'title' => 'How to Track Ticket Status', 'content' => 'You can view the status of your tickets on your Dashboard. Tickets will show as Open, In Progress, Resolved, or Closed. You can also view detailed history by clicking on the ticket reference number.', 'tags' => ['status', 'track', 'history']],
            ['category_id' => 10, 'title' => 'Understanding SLAs', 'content' => 'SLA (Service Level Agreement) dictates the expected response and resolution time for a ticket. If an SLA is breached, it means the ticket has passed its deadline and will be escalated automatically.', 'tags' => ['sla', 'deadline', 'escalate']],
            ['category_id' => 1, 'title' => 'How to Reopen a Ticket', 'content' => 'If an agent resolves your ticket but the issue still persists, you can open the ticket and click "Reopen". This will alert the agent that further work is required. Tickets can only be reopened within the configured reopen window.', 'tags' => ['reopen', 'resolved', 'usage']],
            ['category_id' => 10, 'title' => 'Adding Comments to a Ticket', 'content' => 'To communicate with the assigned agent, open your ticket and scroll to the comments section. Type your message and click submit. You can also attach files up to the maximum allowed file size.', 'tags' => ['comments', 'communication', 'attachments']],
            ['category_id' => 5, 'title' => 'Resetting Your Password', 'content' => 'If you forgot your password, go to the login page and click "Forgot Password". Enter your email to receive a reset link. If you are already logged in, you can change your password in your Profile Settings.', 'tags' => ['password', 'login', 'account']],
            ['category_id' => 10, 'title' => 'How to Escalate a Ticket', 'content' => 'If your ticket is taking too long to be resolved, you can click the "Escalate" button (if available for your role) or request escalation in a comment. Team Leads will be notified of escalated tickets.', 'tags' => ['escalate', 'urgent', 'delay']],
            ['category_id' => 5, 'title' => 'Updating Your Profile', 'content' => 'Click your name in the top right corner and select Profile. Here you can update your name, phone number, and change your password.', 'tags' => ['profile', 'account', 'settings']],
            ['category_id' => 10, 'title' => 'How to Search for Tickets', 'content' => 'Use the search bar in the top navigation to quickly find tickets by their reference number or title. You can also use filters on the All Tickets page to sort by status, priority, and category.', 'tags' => ['search', 'filter', 'find']],
            ['category_id' => 10, 'title' => 'Understanding Ticket Priorities', 'content' => 'Tickets have priorities: Low, Medium, High, Urgent. Choose the priority that accurately reflects the business impact of your issue. Misusing the Urgent priority may result in ticket rejection.', 'tags' => ['priority', 'urgent', 'usage']],

            // Errors and Prevention
            ['category_id' => 8, 'title' => 'Error 403 Forbidden', 'content' => 'A 403 Forbidden error means you do not have permission to view that page or ticket. Ensure you are logged into the correct account. You can only view tickets that you created or are assigned to.', 'tags' => ['error', '403', 'permission']],
            ['category_id' => 2, 'title' => 'Error 404 Not Found', 'content' => 'A 404 Not Found error means the page or ticket you are looking for does not exist. Double-check the URL or search for the ticket reference number using the search bar.', 'tags' => ['error', '404', 'missing']],
            ['category_id' => 8, 'title' => 'Error 419 Page Expired', 'content' => 'A 419 Page Expired error usually happens if you leave the website open for too long without activity. Simply refresh the page and log in again to resolve it.', 'tags' => ['error', '419', 'session']],
            ['category_id' => 7, 'title' => 'Error 500 Internal Server Error', 'content' => 'A 500 Internal Server error means something went wrong on our end. Please wait a few minutes and try again. If it persists, create a new ticket with the "Server Issue" category.', 'tags' => ['error', '500', 'server']],
            ['category_id' => 10, 'title' => 'Attachment Upload Failed', 'content' => 'If your file upload fails, ensure it is smaller than the maximum allowed size (default is usually 10MB or 2MB depending on settings) and that the file format is supported.', 'tags' => ['upload', 'attachment', 'error']],
            ['category_id' => 1, 'title' => 'Emails Not Sending', 'content' => 'If you are not receiving email notifications, check your spam folder. If it is still missing, the system administrator might need to verify the SMTP credentials in the system settings.', 'tags' => ['email', 'notification', 'missing']],
            ['category_id' => 5, 'title' => 'Invalid Login Credentials', 'content' => 'If you see "Invalid credentials", double-check your spelling and ensure CAPS LOCK is off. If you continue to fail, use the Forgot Password link to reset it.', 'tags' => ['login', 'error', 'password']],
            ['category_id' => 10, 'title' => 'Ticket Auto-Closed Automatically', 'content' => 'If a ticket is marked "Resolved", it will automatically change to "Closed" after 7 days (or the configured auto-close window). Once closed, it cannot be reopened; you must create a new ticket.', 'tags' => ['closed', 'resolved', 'auto-close']],
            ['category_id' => 10, 'title' => 'Agent Cannot Be Assigned', 'content' => 'If you are trying to assign a ticket but the agent does not appear in the list, ensure the agent has the "Agent" role and is in the correct department. Agents can also be marked as inactive.', 'tags' => ['assign', 'agent', 'error']],
            ['category_id' => 8, 'title' => 'Session Timeout Prevention', 'content' => 'To prevent being logged out automatically, save your work frequently. If you are typing a long comment, consider typing it in a notepad first, or click "Save Draft" if the feature is available.', 'tags' => ['timeout', 'session', 'prevent']],
        ];

        foreach ($articles as $article) {
            KnowledgeBaseArticle::updateOrCreate(
                ['title' => $article['title']],
                [
                    'author_id' => $admin->id,
                    'category_id' => $article['category_id'],
                    'slug' => Str::slug($article['title']),
                    'content' => $article['content'],
                    'status' => 'published',
                    'tags' => $article['tags'],
                    'published_at' => now(),
                ]
            );
        }
    }
}
