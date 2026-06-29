<?php

use Illuminate\Support\Facades\Route;

// ─── Guest-only routes ────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [App\Http\Controllers\Auth\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [App\Http\Controllers\Auth\AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [App\Http\Controllers\Auth\AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[App\Http\Controllers\Auth\AuthController::class, 'register'])->name('register.post');
    Route::get('/forgot-password',  [App\Http\Controllers\Auth\AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [App\Http\Controllers\Auth\AuthController::class, 'sendReset'])->name('password.email');
    Route::get('/reset-password/{token}',  [App\Http\Controllers\Auth\AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [App\Http\Controllers\Auth\AuthController::class, 'resetPassword'])->name('password.update');
});

// ─── Authenticated routes ─────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Root redirect based on role
    Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/agent-availability', [App\Http\Controllers\DashboardController::class, 'agentAvailability'])->name('dashboard.agent-availability');

    // Logout
    Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');

    // Chatbot
    Route::post('/chatbot/ask', [App\Http\Controllers\SupportAssistantController::class, 'chat'])->name('chatbot.ask');

    // Profile
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::put('/profile/status', [App\Http\Controllers\ProfileController::class, 'updateStatus'])->name('profile.status');

    // ── Tickets ───────────────────────────────────────────────────────────────
    Route::resource('tickets', App\Http\Controllers\TicketController::class);

    Route::prefix('tickets/{ticket}')->name('tickets.')->group(function () {
        Route::post('/assign',   [App\Http\Controllers\TicketController::class, 'assign'])->name('assign');
        Route::post('/accept',   [App\Http\Controllers\TicketController::class, 'accept'])->name('accept');
        Route::post('/reject',   [App\Http\Controllers\TicketController::class, 'reject'])->name('reject');
        Route::post('/escalate', [App\Http\Controllers\TicketController::class, 'escalate'])->name('escalate');
        Route::post('/resolve',  [App\Http\Controllers\TicketController::class, 'resolve'])->name('resolve');
        Route::post('/close',    [App\Http\Controllers\TicketController::class, 'close'])->name('close');
        Route::post('/reopen',   [App\Http\Controllers\TicketController::class, 'reopen'])->name('reopen');
        Route::post('/cancel',   [App\Http\Controllers\TicketController::class, 'cancel'])->name('cancel');
        Route::patch('/status',  [App\Http\Controllers\TicketController::class, 'updateStatus'])->name('status');
        Route::get('/history',   [App\Http\Controllers\TicketController::class, 'history'])->name('history');
    });

    // ── Comments ──────────────────────────────────────────────────────────────
    Route::post('/tickets/{ticket}/comments', [App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}',      [App\Http\Controllers\CommentController::class, 'destroy'])->name('comments.destroy');

    // ── Attachments ───────────────────────────────────────────────────────────
    Route::post('/tickets/{ticket}/attachments', [App\Http\Controllers\AttachmentController::class, 'store'])->name('attachments.store');
    Route::get('/attachments/{attachment}/download', [App\Http\Controllers\AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}',       [App\Http\Controllers\AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // ── Notifications ─────────────────────────────────────────────────────────
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [App\Http\Controllers\NotificationController::class, 'fetchUnread'])->name('notifications.unread');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all',  [App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // ── Reports ───────────────────────────────────────────────────────────────
    Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [App\Http\Controllers\ReportController::class, 'generate'])->name('reports.generate');
    Route::get('/reports/{report}/download', [App\Http\Controllers\ReportController::class, 'download'])->name('reports.download');

    // ── Knowledge Base ────────────────────────────────────────────────────────
    Route::get('/knowledge-base', [App\Http\Controllers\KnowledgeBaseController::class, 'index'])->name('kb.index');
    Route::get('/knowledge-base/{article}', [App\Http\Controllers\KnowledgeBaseController::class, 'show'])->name('kb.show');

    // ── Admin Routes (admin + team_lead) ──────────────────────────────────────
    Route::middleware('role:admin,team_lead')->prefix('admin')->name('admin.')->group(function () {

        // User Management
        Route::resource('users', App\Http\Controllers\Admin\UserController::class);
        Route::post('/users/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/assign-role',   [App\Http\Controllers\Admin\UserController::class, 'assignRole'])->name('users.assign-role');

        // SLA Policies
        Route::resource('sla-policies', App\Http\Controllers\Admin\SlaPolicyController::class)->names([
            'index'   => 'sla.index',
            'create'  => 'sla.create',
            'store'   => 'sla.store',
            'edit'    => 'sla.edit',
            'update'  => 'sla.update',
            'destroy' => 'sla.destroy',
        ]);

        // Team Lead dashboard routes
        Route::get('/sla-monitor', [App\Http\Controllers\Admin\SlaPolicyController::class, 'monitor'])->name('sla.monitor');

        // Knowledge Base Management (Admin & Team Lead)
        Route::resource('kb-articles', App\Http\Controllers\Admin\KbArticleController::class);

        // Admin-only routes
        Route::middleware('role:admin')->group(function () {
            Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
            Route::resource('priorities', App\Http\Controllers\Admin\PriorityController::class);
            Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class);
            Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
            Route::get('/audit-logs', [App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
        });
    });
});
