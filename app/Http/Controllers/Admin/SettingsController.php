<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::orderBy('group_name')->orderBy('key_name')->get()->groupBy('group_name');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach ($request->except('_token', '_method') as $key => $value) {
            SystemSetting::set($key, $value);
        }
        return back()->with('success', 'Settings saved successfully.');
    }
}
