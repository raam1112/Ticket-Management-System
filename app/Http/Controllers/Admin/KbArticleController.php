<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBaseArticle;
use App\Models\TicketCategory;
use Illuminate\Http\Request;

class KbArticleController extends Controller
{
    public function index()
    {
        $articles = KnowledgeBaseArticle::with('author','category')->latest()->paginate(20);
        return view('admin.kb.index', compact('articles'));
    }
    public function create()
    {
        return view('admin.kb.create', ['categories' => TicketCategory::active()->get()]);
    }
    public function store(Request $request)
    {
        $request->validate(['title'=>'required|string|max:255','content'=>'required|string','category_id'=>'nullable|exists:ticket_categories,id','status'=>'required|in:draft,published,archived']);
        KnowledgeBaseArticle::create(array_merge($request->only('title','content','category_id','status','tags'), ['slug' => \Str::slug($request->title).'-'.time(), 'author_id' => auth()->id(), 'published_at' => $request->status === 'published' ? now() : null]));
        return redirect()->route('admin.kb-articles.index')->with('success','Article created.');
    }
    public function edit(KnowledgeBaseArticle $kbArticle)
    {
        return view('admin.kb.edit', ['article'=>$kbArticle,'categories'=>TicketCategory::active()->get()]);
    }
    public function update(Request $request, KnowledgeBaseArticle $kbArticle)
    {
        $kbArticle->update($request->only('title','content','category_id','status','tags'));
        return back()->with('success','Article updated.');
    }
    public function destroy(KnowledgeBaseArticle $kbArticle) { $kbArticle->delete(); return back()->with('success','Deleted.'); }
}
