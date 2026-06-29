<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBaseArticle;
use App\Models\TicketCategory;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        $query = KnowledgeBaseArticle::published()->with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('content', 'like', "%{$s}%");
            });
        }

        $articles = $query->latest('published_at')->paginate(12)->withQueryString();
        $categories = TicketCategory::active()->has('knowledgeBaseArticles')->get();

        return view('kb.index', compact('articles', 'categories'));
    }

    public function show(KnowledgeBaseArticle $article)
    {
        abort_unless($article->status === 'published', 404);

        // Increment view count using a simple session-based approach to prevent spam
        $sessionKey = 'kb_viewed_' . $article->id;
        if (!session()->has($sessionKey)) {
            $article->increment('view_count');
            session()->put($sessionKey, true);
        }

        $related = KnowledgeBaseArticle::published()
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->take(5)
            ->get();

        return view('kb.show', compact('article', 'related'));
    }
}
