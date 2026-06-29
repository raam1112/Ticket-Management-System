@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800" style="font-weight: 300;">System Settings</h1>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf
    
    <div class="row">
        <!-- Sidebar Navigation for Settings -->
        <div class="col-md-3 mb-4">
            <div class="list-group shadow-sm" id="list-tab" role="tablist" style="border-radius: 16px; overflow: hidden; border: none;">
                @foreach($settings as $group => $items)
                    <a class="list-group-item list-group-item-action border-0 {{ $loop->first ? 'active' : '' }}" id="list-{{ Str::slug($group) }}-list" data-bs-toggle="list" href="#list-{{ Str::slug($group) }}" role="tab" aria-controls="{{ Str::slug($group) }}" style="padding: 1rem 1.25rem;">
                        <i class="fas fa-cogs fa-fw me-2"></i> {{ ucfirst($group) }}
                    </a>
                @endforeach
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary bg-gradient w-100 shadow-sm rounded-pill py-2 font-weight-bold" style="letter-spacing: 0.5px;"><i class="fas fa-save me-2"></i> Save All Settings</button>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="col-md-9">
            <div class="card shadow-lg border-0" style="border-radius: 16px;">
                <div class="card-body p-5">
                    <div class="tab-content" id="nav-tabContent">
                        @foreach($settings as $group => $items)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="list-{{ Str::slug($group) }}" role="tabpanel" aria-labelledby="list-{{ Str::slug($group) }}-list">
                                <h4 class="mb-4 text-primary font-weight-bold border-bottom pb-3">{{ ucfirst($group) }} Settings</h4>
                                
                                @foreach($items as $setting)
                                    <div class="mb-4">
                                        <label class="form-label font-weight-bold text-gray-800">{{ $setting->display_name }}</label>
                                        
                                        @if($setting->type === 'boolean')
                                            <div class="form-check form-switch mt-1">
                                                <input type="hidden" name="{{ $setting->key_name }}" value="0">
                                                <input class="form-check-input" type="checkbox" name="{{ $setting->key_name }}" value="1" {{ $setting->value ? 'checked' : '' }} style="width: 3rem; height: 1.5rem;">
                                            </div>
                                        @elseif($setting->type === 'integer')
                                            <input type="number" name="{{ $setting->key_name }}" class="form-control" style="border-radius: 8px;" value="{{ $setting->value }}">
                                        @elseif($setting->type === 'text')
                                            <textarea name="{{ $setting->key_name }}" class="form-control" style="border-radius: 8px;" rows="3">{{ $setting->value }}</textarea>
                                        @elseif($setting->type === 'json')
                                            <textarea name="{{ $setting->key_name }}" class="form-control font-monospace bg-light" style="border-radius: 8px;" rows="4">{{ $setting->value }}</textarea>
                                        @else
                                            <input type="text" name="{{ $setting->key_name }}" class="form-control" style="border-radius: 8px;" value="{{ $setting->value }}">
                                        @endif
                                        
                                        @if($setting->description)
                                            <small class="form-text text-muted mt-2 d-block"><i class="fas fa-info-circle me-1"></i>{{ $setting->description }}</small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
