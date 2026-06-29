@extends('layouts.app')

@section('title', 'SLA Policies')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800" style="font-weight: 300;">SLA Policies</h1>
    <div>
        <a href="{{ route('admin.sla.monitor') }}" class="btn btn-info bg-gradient shadow-sm me-2 rounded-pill px-4 py-2 font-weight-bold" style="letter-spacing: 0.5px;"><i class="fas fa-desktop fa-sm text-white-50 me-2"></i> SLA Monitor</a>
        <button class="btn btn-primary bg-gradient shadow-sm rounded-pill px-4 py-2 font-weight-bold" style="letter-spacing: 0.5px;" data-bs-toggle="modal" data-bs-target="#createPolicyModal"><i class="fas fa-plus fa-sm text-white-50 me-2"></i> Create Policy</button>
    </div>
</div>

<div class="card shadow-lg mb-4" style="border-radius: 16px; border: none; overflow: hidden;">
    <div class="card-header py-4 bg-white d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #f1f3f9;">
        <h6 class="m-0 font-weight-bold text-primary" style="font-size: 16px;"><i class="fas fa-shield-alt text-primary me-2"></i>Configured SLA Policies</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                <thead class="bg-light text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="px-4 py-3 border-0 rounded-top-left">Policy Name</th>
                        <th class="py-3 border-0">Target Scope</th>
                        <th class="py-3 border-0 text-center">Response Time</th>
                        <th class="py-3 border-0 text-center">Resolution Time</th>
                        <th class="py-3 border-0 text-center">Escalation</th>
                        <th class="py-3 border-0 text-center">Status</th>
                        <th class="px-4 py-3 border-0 text-center rounded-top-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($policies as $policy)
                        <tr style="transition: all 0.2s ease;">
                            <td class="px-4 py-3 border-bottom-0 font-weight-bold text-dark">{{ $policy->name }}</td>
                            <td class="py-3 border-bottom-0 text-gray-700">
                                @if($policy->category_id && $policy->priority_id)
                                    Category: <strong>{{ $policy->category->name }}</strong> + Priority: <strong>{{ $policy->priority->display_name }}</strong>
                                @elseif($policy->category_id)
                                    Category: <strong>{{ $policy->category->name }}</strong>
                                @elseif($policy->priority_id)
                                    Priority: <strong>{{ $policy->priority->display_name }}</strong>
                                @else
                                    <em class="text-muted">System Default</em>
                                @endif
                            </td>
                            <td class="py-3 border-bottom-0 text-center text-primary font-weight-bold">{{ $policy->response_time_hours }} hrs</td>
                            <td class="py-3 border-bottom-0 text-center text-success font-weight-bold">{{ $policy->resolution_time_hours }} hrs</td>
                            <td class="py-3 border-bottom-0 text-center text-warning font-weight-bold">{{ $policy->escalation_after_hours }} hrs</td>
                            <td class="py-3 border-bottom-0 text-center">
                                @if($policy->is_active)
                                    <span class="badge bg-success bg-gradient rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Active</span>
                                @else
                                    <span class="badge bg-secondary bg-gradient rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Disabled</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 border-bottom-0 text-center">
                                <button class="btn btn-sm btn-light rounded-circle shadow-sm me-1" style="width: 32px; height: 32px;" data-bs-toggle="modal" data-bs-target="#editPolicyModal{{ $policy->id }}"><i class="fas fa-edit text-primary"></i></button>
                                <form action="{{ route('admin.sla.destroy', $policy) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light rounded-circle shadow-sm" style="width: 32px; height: 32px;" onclick="return confirm('Delete this policy?')"><i class="fas fa-trash text-danger"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editPolicyModal{{ $policy->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 1rem 3rem rgba(0,0,0,.175);">
                                    <form action="{{ route('admin.sla.update', $policy) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title font-weight-bold text-primary">Edit Policy</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label font-weight-bold">Policy Name</label>
                                                <input type="text" name="name" class="form-control" style="border-radius: 8px;" value="{{ $policy->name }}" required>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label font-weight-bold">Response Time (Hours)</label>
                                                    <input type="number" name="response_time_hours" class="form-control" style="border-radius: 8px;" value="{{ $policy->response_time_hours }}" required min="1">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label font-weight-bold">Resolution Time (Hours)</label>
                                                    <input type="number" name="resolution_time_hours" class="form-control" style="border-radius: 8px;" value="{{ $policy->resolution_time_hours }}" required min="1">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label font-weight-bold">Auto Escalate After (Hours)</label>
                                                    <input type="number" name="escalation_after_hours" class="form-control" style="border-radius: 8px;" value="{{ $policy->escalation_after_hours }}" required min="0">
                                                </div>
                                            </div>
                                            <div class="form-check form-switch mb-3">
                                                <input type="hidden" name="is_active" value="0">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $policy->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label font-weight-bold">Policy Active</label>
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
                                    <i class="fas fa-shield-alt text-primary mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <h5>No Policies Configured</h5>
                                    <p class="mb-0">The system will use Priority defaults.</p>
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
<div class="modal fade" id="createPolicyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 1rem 3rem rgba(0,0,0,.175);">
            <form action="{{ route('admin.sla.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-primary">Create SLA Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Policy Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" style="border-radius: 8px;" required placeholder="e.g. Critical Bug Policy">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Target Category</label>
                            <select name="category_id" class="form-select" style="border-radius: 8px;">
                                <option value="">Any Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Target Priority</label>
                            <select name="priority_id" class="form-select" style="border-radius: 8px;">
                                <option value="">Any Priority</option>
                                @foreach($priorities as $pri)
                                    <option value="{{ $pri->id }}">{{ $pri->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-info small" style="border-radius: 8px; border: none; border-left: 4px solid #36b9cc;">
                        <i class="fas fa-info-circle me-2"></i> If both Category and Priority are selected, this policy will only apply when a ticket matches BOTH. If only one is selected, it applies when that condition is met. Specific rules override generic rules.
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Response Time (Hours) <span class="text-danger">*</span></label>
                            <input type="number" name="response_time_hours" class="form-control" style="border-radius: 8px;" required min="1" value="24">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Resolution Time (Hours) <span class="text-danger">*</span></label>
                            <input type="number" name="resolution_time_hours" class="form-control" style="border-radius: 8px;" required min="1" value="48">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Escalate After (Hours) <span class="text-danger">*</span></label>
                            <input type="number" name="escalation_after_hours" class="form-control" style="border-radius: 8px;" required min="0" value="0">
                            <small class="text-muted">0 = No auto escalation</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary bg-gradient rounded-pill px-4 shadow-sm">Create Policy</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
