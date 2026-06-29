@extends('layouts.app')

@section('title', 'Manage Departments')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800" style="font-weight: 300;">Departments</h1>
    <button class="btn btn-primary bg-gradient shadow-sm rounded-pill px-4 py-2 font-weight-bold" style="letter-spacing: 0.5px;" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fas fa-plus fa-sm text-white-50 me-2"></i> Add Department</button>
</div>

<div class="card shadow-lg mb-4" style="border-radius: 16px; border: none; overflow: hidden;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                <thead class="bg-light text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="px-4 py-3 border-0 rounded-top-left">Code</th>
                        <th class="py-3 border-0">Name</th>
                        <th class="py-3 border-0">Description</th>
                        <th class="py-3 border-0 text-center">Staff Count</th>
                        <th class="py-3 border-0 text-center">Status</th>
                        <th class="px-4 py-3 border-0 text-center rounded-top-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($departments as $dept)
                        <tr style="transition: all 0.2s ease;">
                            <td class="px-4 py-3 border-bottom-0 font-weight-bold text-dark">{{ $dept->code }}</td>
                            <td class="py-3 border-bottom-0 text-gray-700 font-weight-bold">{{ $dept->name }}</td>
                            <td class="py-3 border-bottom-0 text-muted">{{ Str::limit($dept->description, 50) }}</td>
                            <td class="py-3 border-bottom-0 text-center font-weight-bold text-primary">{{ $dept->users_count }}</td>
                            <td class="py-3 border-bottom-0 text-center">
                                @if($dept->is_active)
                                    <span class="badge bg-success bg-gradient rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Active</span>
                                @else
                                    <span class="badge bg-secondary bg-gradient rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 border-bottom-0 text-center">
                                <button class="btn btn-sm btn-light rounded-circle shadow-sm me-1" style="width: 32px; height: 32px;" data-bs-toggle="modal" data-bs-target="#editModal{{ $dept->id }}"><i class="fas fa-edit text-primary"></i></button>
                                <form action="{{ route('admin.departments.destroy', $dept) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light rounded-circle shadow-sm" style="width: 32px; height: 32px;" onclick="return confirm('Delete department?')"><i class="fas fa-trash text-danger"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal{{ $dept->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 1rem 3rem rgba(0,0,0,.175);">
                                    <form action="{{ route('admin.departments.update', $dept) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title font-weight-bold text-primary">Edit Department</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label font-weight-bold">Name</label>
                                                <input type="text" name="name" class="form-control" style="border-radius: 8px;" value="{{ $dept->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label font-weight-bold">Description</label>
                                                <textarea name="description" class="form-control" style="border-radius: 8px;" rows="3">{{ $dept->description }}</textarea>
                                            </div>
                                            <div class="form-check form-switch mb-3">
                                                <input type="hidden" name="is_active" value="0">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $dept->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label font-weight-bold">Active</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0">
                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary bg-gradient rounded-pill px-4 shadow-sm">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted border-0">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <i class="fas fa-building text-primary mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <h5>No Departments Found</h5>
                                    <p class="mb-0">Create the first department.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 1rem 3rem rgba(0,0,0,.175);">
            <form action="{{ route('admin.departments.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-primary">Add Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Code (Unique, e.g. IT, HR, FIN)</label>
                        <input type="text" name="code" class="form-control" style="border-radius: 8px;" required maxlength="20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Name</label>
                        <input type="text" name="name" class="form-control" style="border-radius: 8px;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Description</label>
                        <textarea name="description" class="form-control" style="border-radius: 8px;" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary bg-gradient rounded-pill px-4 shadow-sm">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
