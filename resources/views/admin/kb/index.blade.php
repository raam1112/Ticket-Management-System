@extends('layouts.app')

@section('title', 'Manage Knowledge Base')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800" style="font-weight: 300;">Manage KB Articles</h1>
    <a href="{{ route('admin.kb-articles.create') }}" class="btn btn-primary bg-gradient shadow-sm rounded-pill px-4 py-2 font-weight-bold" style="letter-spacing: 0.5px;"><i class="fas fa-plus fa-sm text-white-50 me-2"></i> Add New Article</a>
</div>

<div class="card shadow-lg mb-4" style="border-radius: 16px; border: none; overflow: hidden;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                <thead class="bg-light text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="px-4 py-3 border-0 rounded-top-left">Title</th>
                        <th class="py-3 border-0">Category</th>
                        <th class="py-3 border-0 text-center">Status</th>
                        <th class="py-3 border-0 text-center">Views</th>
                        <th class="py-3 border-0">Published</th>
                        <th class="px-4 py-3 border-0 text-center rounded-top-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($articles as $article)
                        <tr style="transition: all 0.2s ease;">
                            <td class="px-4 py-3 border-bottom-0 font-weight-bold text-dark">{{ Str::limit($article->title, 40) }}</td>
                            <td class="py-3 border-bottom-0 text-gray-700 font-weight-medium">
                                <i class="fas fa-folder text-muted me-2"></i>{{ $article->category?->name ?? 'General' }}
                            </td>
                            <td class="py-3 border-bottom-0 text-center">
                                @if($article->status === 'published')
                                    <span class="badge bg-success bg-gradient rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Published</span>
                                @elseif($article->status === 'draft')
                                    <span class="badge bg-warning text-dark bg-gradient rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Draft</span>
                                @else
                                    <span class="badge bg-secondary bg-gradient rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Archived</span>
                                @endif
                            </td>
                            <td class="py-3 border-bottom-0 text-center">
                                <span class="badge bg-light text-dark rounded-pill px-3 py-1 border shadow-sm">{{ $article->view_count }}</span>
                            </td>
                            <td class="py-3 border-bottom-0 text-gray-500 font-weight-medium">
                                <i class="far fa-calendar-alt me-1 text-primary"></i> {{ $article->published_at ? $article->published_at->format('Y-m-d') : 'N/A' }}
                            </td>
                            <td class="px-4 py-3 border-bottom-0 text-center">
                                <a href="{{ route('admin.kb-articles.edit', $article) }}" class="btn btn-sm btn-light rounded-circle shadow-sm me-1" style="width: 32px; height: 32px;"><i class="fas fa-edit text-primary"></i></a>
                                <form action="{{ route('admin.kb-articles.destroy', $article) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light rounded-circle shadow-sm" style="width: 32px; height: 32px;" onclick="return confirm('Delete this article?')"><i class="fas fa-trash text-danger"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted border-0">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <i class="fas fa-book-open text-primary mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <h5>No KB Articles Found</h5>
                                    <p class="mb-0">Start building your knowledge base today!</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($articles->hasPages())
        <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">{{ $articles->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
