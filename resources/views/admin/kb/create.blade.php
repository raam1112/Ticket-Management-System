@extends('layouts.app')

@section('title', 'Add KB Article')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Add KB Article</h1>
    <a href="{{ route('admin.kb-articles.index') }}" class="btn btn-sm btn-secondary shadow-sm"><i class="fas fa-arrow-left fa-sm text-white-50"></i> Back</a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ route('admin.kb-articles.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label font-weight-bold">Title</label>
                <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">General</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="draft">Draft (Hidden)</option>
                        <option value="published">Published (Visible)</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label font-weight-bold">Content (Supports Markdown/HTML)</label>
                <textarea name="content" class="form-control" rows="10" required>{{ old('content') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="form-label font-weight-bold">Tags (Comma separated JSON array e.g. ["guide","help"])</label>
                <input type="text" name="tags" class="form-control" value='["guide"]'>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Article</button>
        </form>
    </div>
</div>
@endsection
