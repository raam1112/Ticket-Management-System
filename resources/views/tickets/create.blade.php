@extends('layouts.app')

@section('title', 'Create Ticket')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single { height: 38px; border: 1px solid #ced4da; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
</style>
@endpush

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create New Ticket</h1>
    <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-secondary shadow-sm"><i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Tickets</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4 border-left-primary">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Ticket Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Title / Subject <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required autofocus placeholder="Brief summary of the issue">
                        @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select select2 @error('category_id') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Priority <span class="text-danger">*</span></label>
                            <select name="priority_id" class="form-select @error('priority_id') is-invalid @enderror" required>
                                <option value="">Select Priority</option>
                                @foreach($priorities as $pri)
                                    <option value="{{ $pri->id }}" {{ old('priority_id') == $pri->id ? 'selected' : '' }}>{{ $pri->display_name }}</option>
                                @endforeach
                            </select>
                            @error('priority_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="6" required placeholder="Please provide detailed information to help us resolve your request...">{{ old('description') }}</textarea>
                        @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        <small class="text-muted">Minimum 20 characters.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Tags (Comma separated)</label>
                        <input type="text" name="tags" class="form-control @error('tags') is-invalid @enderror" value="{{ old('tags') }}" placeholder="e.g. login, bug, urgent">
                        @error('tags')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <hr>

                    <div class="mb-4">
                        <label class="form-label font-weight-bold">Attachments</label>
                        <input type="file" name="attachments[]" class="form-control @error('attachments.*') is-invalid @enderror" multiple>
                        <small class="text-muted">Allowed types: jpg, png, pdf, docx, xlsx, zip. Max size: 10MB per file. Up to 5 files.</small>
                        @error('attachments.*')<span class="text-danger small d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="reset" class="btn btn-secondary me-2">Clear Form</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle"></i> Helpful Tips</h6>
            </div>
            <div class="card-body">
                <p>Before submitting a ticket, please check our <a href="{{ route('kb.index') }}">Knowledge Base</a>. The answer to your question might already be there!</p>
                <hr>
                <strong>How to write a good ticket:</strong>
                <ul class="pl-3 mt-2 mb-0">
                    <li class="mb-2">Be descriptive in your title.</li>
                    <li class="mb-2">Provide step-by-step instructions to reproduce issues.</li>
                    <li class="mb-2">Include screenshots or error logs if applicable.</li>
                    <li>Choose the correct category and priority so we can route it properly.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: "default"
        });
    });
</script>
@endpush
