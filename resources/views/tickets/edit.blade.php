@extends('layouts.app')

@section('title', 'Edit Ticket - ' . $ticket->reference_number)

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
    <h1 class="h3 mb-0 text-gray-800">Edit Ticket <span class="text-primary">{{ $ticket->reference_number }}</span></h1>
    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-secondary shadow-sm"><i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Ticket</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4 border-left-warning">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-edit"></i> Edit Ticket Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Title / Subject <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $ticket->title) }}" required autofocus placeholder="Brief summary of the issue">
                        @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select select2 @error('category_id') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $ticket->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Priority <span class="text-danger">*</span></label>
                            <select name="priority_id" class="form-select @error('priority_id') is-invalid @enderror" required>
                                <option value="">Select Priority</option>
                                @foreach($priorities as $pri)
                                    <option value="{{ $pri->id }}" {{ old('priority_id', $ticket->priority_id) == $pri->id ? 'selected' : '' }}>{{ $pri->display_name }}</option>
                                @endforeach
                            </select>
                            @error('priority_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="6" required placeholder="Please provide detailed information...">{{ old('description', $ticket->description) }}</textarea>
                        @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        <small class="text-muted">Minimum 20 characters.</small>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle"></i> Ticket Info</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th class="text-muted">Reference</th>
                        <td>{{ $ticket->reference_number }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Status</th>
                        <td><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Created</th>
                        <td>{{ $ticket->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Last Updated</th>
                        <td>{{ $ticket->updated_at->diffForHumans() }}</td>
                    </tr>
                </table>
                <hr>
                <div class="alert alert-warning mb-0 small">
                    <i class="fas fa-exclamation-triangle"></i> You can only edit tickets that are in <strong>Open</strong> status (or if you are an Admin).
                </div>
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
