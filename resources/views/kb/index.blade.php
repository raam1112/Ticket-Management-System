@extends('layouts.app')

@section('title', 'Knowledge Base')

@section('content')
<div class="text-center mb-5 mt-3">
    <h1 class="display-5 font-weight-bold text-gray-900">How can we help you?</h1>
    <p class="lead text-muted mb-4">Search our knowledge base for quick answers.</p>
    
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <form action="{{ route('kb.index') }}" method="GET">
                <div class="input-group input-group-lg shadow-sm">
                    <input type="text" name="search" class="form-control border-0 bg-white" placeholder="Search for articles, guides, or FAQs..." value="{{ request('search') }}">
                    <button class="btn btn-primary px-4" type="submit"><i class="fas fa-search"></i> Search</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <!-- Categories Sidebar -->
    <div class="col-lg-3 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h6 class="m-0 font-weight-bold text-gray-800 text-uppercase">Categories</h6>
            </div>
            <div class="card-body p-0 pt-2 pb-3">
                <div class="list-group list-group-flush">
                    <a href="{{ route('kb.index') }}" class="list-group-item list-group-item-action {{ !request('category_id') ? 'active font-weight-bold bg-primary text-white' : '' }} border-0">
                        <i class="fas fa-layer-group fa-fw me-2"></i> All Articles
                    </a>
                    @foreach($categories as $category)
                        <a href="{{ route('kb.index', ['category_id' => $category->id]) }}" class="list-group-item list-group-item-action {{ request('category_id') == $category->id ? 'active font-weight-bold bg-primary text-white' : '' }} border-0">
                            <span style="color:{{ request('category_id') == $category->id ? '#fff' : $category->color }}"><i class="fas {{ $category->icon ?? 'fa-folder' }} fa-fw me-2"></i></span> {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        
        @if(auth()->user()->hasAnyRole(['admin', 'team_lead']))
        <div class="mt-4 text-center">
            <a href="{{ route('admin.kb-articles.index') }}" class="btn btn-outline-secondary w-100"><i class="fas fa-cog"></i> Manage KB Articles</a>
        </div>
        @endif
    </div>

    <!-- Article List -->
    <div class="col-lg-9">
        @if(request('search'))
            <h5 class="mb-4">Search results for: <strong>"{{ request('search') }}"</strong></h5>
        @endif

        <div class="row">
            @forelse($articles as $article)
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0 kb-card transition-all" style="transition: transform 0.2s, box-shadow 0.2s;">
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="badge bg-light text-dark border" style="border-color:{{ $article->category?->color }} !important;">
                                    <i class="fas {{ $article->category?->icon ?? 'fa-folder' }}" style="color:{{ $article->category?->color }}"></i> {{ $article->category?->name ?? 'General' }}
                                </span>
                            </div>
                            <h5 class="card-title font-weight-bold mb-2">
                                <a href="{{ route('kb.show', $article) }}" class="text-gray-900 text-decoration-none stretched-link">{{ $article->title }}</a>
                            </h5>
                            <p class="card-text text-muted small mb-3">
                                {{ Str::limit(strip_tags($article->content), 120) }}
                            </p>
                        </div>
                        <div class="card-footer bg-white border-top-0 pt-0 pb-3 d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="fas fa-clock me-1"></i> {{ $article->published_at->diffForHumans() }}</small>
                            <small class="text-muted"><i class="fas fa-eye me-1"></i> {{ $article->view_count }} views</small>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="text-muted mb-3"><i class="fas fa-search fa-3x"></i></div>
                    <h5 class="text-gray-800 font-weight-bold">No articles found</h5>
                    <p class="text-muted">We couldn't find any articles matching your criteria.</p>
                    <a href="{{ route('kb.index') }}" class="btn btn-primary">Clear Filters</a>
                </div>
            @endforelse
        </div>

        @if($articles->hasPages())
            <div class="mt-4">
                {{ $articles->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('css')
<style>
    .kb-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
</style>
@endpush
