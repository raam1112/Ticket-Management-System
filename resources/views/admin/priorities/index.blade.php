@extends('layouts.app')

@section('title', 'Manage Priorities')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800" style="font-weight: 300;">Priorities</h1>
    <button class="btn btn-primary bg-gradient shadow-sm rounded-pill px-4 py-2 font-weight-bold" style="letter-spacing: 0.5px;" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fas fa-plus fa-sm text-white-50 me-2"></i> Add Priority</button>
</div>

<div class="card shadow-lg mb-4" style="border-radius: 16px; border: none; overflow: hidden;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                <thead class="bg-light text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="px-4 py-3 border-0 rounded-top-left text-center">Order</th>
                        <th class="py-3 border-0">Name (Code)</th>
                        <th class="py-3 border-0">Display Name</th>
                        <th class="py-3 border-0 text-center">Visuals</th>
                        <th class="py-3 border-0 text-center">SLA (Res/Res)</th>
                        <th class="py-3 border-0 text-center">Status</th>
                        <th class="px-4 py-3 border-0 text-center rounded-top-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($priorities as $pri)
                        <tr style="transition: all 0.2s ease;">
                            <td class="px-4 py-3 border-bottom-0 text-center font-weight-bold text-muted">{{ $pri->sort_order }}</td>
                            <td class="py-3 border-bottom-0 font-weight-bold text-dark">{{ $pri->name }}</td>
                            <td class="py-3 border-bottom-0 font-weight-bold text-gray-700">{{ $pri->display_name }}</td>
                            <td class="py-3 border-bottom-0 text-center">
                                <span class="badge bg-light rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border: 2px solid {{ $pri->color }} !important;">
                                    <i class="fas {{ $pri->icon ?? 'fa-exclamation' }}" style="color:{{ $pri->color }}; font-size: 14px;"></i>
                                </span>
                            </td>
                            <td class="py-3 border-bottom-0 text-center font-weight-bold text-primary">{{ $pri->sla_hours_response }}h / {{ $pri->sla_hours_resolve }}h</td>
                            <td class="py-3 border-bottom-0 text-center">
                                @if($pri->is_active)
                                    <span class="badge bg-success bg-gradient rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Active</span>
                                @else
                                    <span class="badge bg-secondary bg-gradient rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 border-bottom-0 text-center">
                                <button class="btn btn-sm btn-light rounded-circle shadow-sm me-1" style="width: 32px; height: 32px;" data-bs-toggle="modal" data-bs-target="#editModal{{ $pri->id }}"><i class="fas fa-edit text-primary"></i></button>
                                <form action="{{ route('admin.priorities.destroy', $pri) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light rounded-circle shadow-sm" style="width: 32px; height: 32px;" onclick="return confirm('Delete priority?')"><i class="fas fa-trash text-danger"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal{{ $pri->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 1rem 3rem rgba(0,0,0,.175);">
                                    <form action="{{ route('admin.priorities.update', $pri) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title font-weight-bold text-primary">Edit Priority</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label font-weight-bold">Display Name</label>
                                                    <input type="text" name="display_name" class="form-control" style="border-radius: 8px;" value="{{ $pri->display_name }}" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label font-weight-bold">Color Hex</label>
                                                    <input type="color" name="color" class="form-control form-control-color w-100" style="border-radius: 8px; height: 38px;" value="{{ $pri->color ?? '#4e73df' }}" required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label font-weight-bold">Default SLA Response (Hrs)</label>
                                                    <input type="number" name="sla_hours_response" class="form-control" style="border-radius: 8px;" value="{{ $pri->sla_hours_response }}" min="1" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label font-weight-bold">Default SLA Resolve (Hrs)</label>
                                                    <input type="number" name="sla_hours_resolve" class="form-control" style="border-radius: 8px;" value="{{ $pri->sla_hours_resolve }}" min="1" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label font-weight-bold">Sort Order</label>
                                                <input type="number" name="sort_order" class="form-control" style="border-radius: 8px;" value="{{ $pri->sort_order }}" min="0">
                                            </div>
                                            <div class="form-check form-switch mb-3">
                                                <input type="hidden" name="is_active" value="0">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $pri->is_active ? 'checked' : '' }}>
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
                            <td colspan="7" class="text-center py-5 text-muted border-0">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <i class="fas fa-exclamation-triangle text-primary mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <h5>No Priorities Found</h5>
                                    <p class="mb-0">Create the first priority level.</p>
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
            <form action="{{ route('admin.priorities.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-primary">Add Priority</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Name (Code e.g. low, high)</label>
                            <input type="text" name="name" class="form-control" style="border-radius: 8px;" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Display Name (e.g. Low, Critical)</label>
                            <input type="text" name="display_name" class="form-control" style="border-radius: 8px;" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Color Hex</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" style="border-radius: 8px; height: 38px;" value="#4e73df" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" style="border-radius: 8px;" value="0" min="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Default SLA Response (Hrs)</label>
                            <input type="number" name="sla_hours_response" class="form-control" style="border-radius: 8px;" value="24" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Default SLA Resolve (Hrs)</label>
                            <input type="number" name="sla_hours_resolve" class="form-control" style="border-radius: 8px;" value="48" min="1" required>
                        </div>
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
