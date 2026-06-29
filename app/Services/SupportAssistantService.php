<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\KnowledgeBaseArticle;
use Illuminate\Support\Facades\Auth;

class SupportAssistantService
{
    public function processQuery(string $query, $user): array
    {
        $queryLower = strtolower($query);

        // 1. Check for personal ticket status
        if (str_contains($queryLower, 'my tickets') || str_contains($queryLower, 'ticket status')) {
            if (!$user) {
                return ['type' => 'text', 'message' => 'Please log in to view your tickets.'];
            }
            $openTickets = Ticket::where('created_by', $user->id)
                ->whereNotIn('status', ['resolved', 'closed'])
                ->count();
            return [
                'type' => 'text',
                'message' => "You currently have {$openTickets} open ticket(s). You can view them in your Dashboard."
            ];
        }

        // 2. Search Knowledge Base from Database
        // We will perform a simple LIKE query for any matching words
        $words = array_filter(explode(' ', $queryLower), fn($w) => strlen($w) > 3);
        
        $articlesQuery = KnowledgeBaseArticle::published();
        
        if (count($words) > 0) {
            $articlesQuery->where(function($q) use ($words) {
                foreach ($words as $word) {
                    $q->orWhere('title', 'LIKE', '%' . $word . '%')
                      ->orWhere('content', 'LIKE', '%' . $word . '%')
                      ->orWhere('tags', 'LIKE', '%' . $word . '%');
                }
            });
        }
        
        $article = count($words) > 0 ? $articlesQuery->first() : null;

        if ($article) {
            return [
                'type' => 'text',
                'message' => "<strong>{$article->title}</strong><br>" . $article->content . "<br><br><small><a href='" . route('kb.show', $article->id) . "' target='_blank'>View Full Article</a></small>"
            ];
        }

        // 3. Fallback
        return [
            'type' => 'text',
            'message' => "I'm sorry, I couldn't find an exact answer for that. Try asking about specific features like 'How to create a ticket', 'Reset password', or 'Error 403'."
        ];
    }
}
