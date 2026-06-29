@extends('layouts.app')

@section('title', $article->title . ' - Knowledge Base')

@section('content')
<div class="mb-4">
    <a href="{{ route('kb.index') }}" class="text-decoration-none text-muted"><i class="fas fa-arrow-left me-1"></i> Back to Knowledge Base</a>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-5">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb bg-transparent p-0 m-0 small">
                        <li class="breadcrumb-item"><a href="{{ route('kb.index') }}">KB Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('kb.index', ['category_id' => $article->category_id]) }}">{{ $article->category?->name ?? 'General' }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($article->title, 30) }}</li>
                    </ol>
                </nav>

                <h1 class="h2 font-weight-bold text-gray-900 mb-3">{{ $article->title }}</h1>
                
                <div class="d-flex align-items-center mb-4 pb-4 border-bottom text-muted small">
                    <div class="me-4"><i class="fas fa-user-edit me-1"></i> Written by {{ $article->author?->name ?? 'System' }}</div>
                    <div class="me-4"><i class="fas fa-calendar-alt me-1"></i> Published {{ $article->published_at->format('M d, Y') }}</div>
                    <div><i class="fas fa-eye me-1"></i> {{ $article->view_count }} views</div>
                </div>

                <div class="kb-content" style="font-size: 1.05rem; line-height: 1.7;">
                    {!! nl2br(e($article->content)) !!}
                </div>

                @if($article->tags)
                    <div class="mt-5 pt-4 border-top">
                        <h6 class="font-weight-bold text-gray-800 mb-2">Tags:</h6>
                        @foreach(is_array($article->tags) ? $article->tags : json_decode($article->tags, true) as $tag)
                            <span class="badge bg-light text-dark border me-1">{{ trim($tag) }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="card-footer bg-light p-4 text-center border-0 rounded-bottom">
                <h6 class="font-weight-bold text-gray-800 mb-3">Was this article helpful?</h6>
                <button class="btn btn-outline-success me-2 px-4 rounded-pill"><i class="fas fa-thumbs-up me-1"></i> Yes</button>
                <button class="btn btn-outline-danger px-4 rounded-pill"><i class="fas fa-thumbs-down me-1"></i> No</button>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Help Box -->
        <div class="card shadow-sm border-0 bg-primary text-white mb-4">
            <div class="card-body text-center p-4">
                <div class="mb-3"><i class="fas fa-life-ring fa-3x opacity-50"></i></div>
                <h5 class="font-weight-bold">Still need help?</h5>
                <p class="small mb-4 opacity-75">If you couldn't find the answer to your question, our support team is ready to assist you.</p>
                <a href="{{ route('tickets.create') }}" class="btn btn-light text-primary font-weight-bold w-100 rounded-pill shadow-sm">Submit a Ticket</a>
            </div>
        </div>

        <!-- Related Articles -->
        @if($related->count() > 0)
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white pt-4 pb-2 border-0">
                <h6 class="m-0 font-weight-bold text-gray-800">Related Articles</h6>
            </div>
            <div class="card-body p-0 pb-3">
                <div class="list-group list-group-flush">
                    @foreach($related as $rel)
                        <a href="{{ route('kb.show', $rel) }}" class="list-group-item list-group-item-action border-0 py-3">
                            <h6 class="mb-1 font-weight-bold text-gray-800">{{ $rel->title }}</h6>
                            <small class="text-muted">{{ Str::limit(strip_tags($rel->content), 60) }}</small>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
