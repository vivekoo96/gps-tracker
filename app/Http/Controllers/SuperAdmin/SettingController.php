<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\GlobalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = GlobalSetting::pluck('value', 'key');
        return view('super-admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'support_email' => 'required|email|max:255',
            'footer_text' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:1024',
            'razorpay_key' => 'nullable|string',
            'razorpay_secret' => 'nullable|string',
        ]);

        // Handle File Uploads
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('settings', 'public');
            GlobalSetting::updateOrCreate(['key' => 'logo'], ['value' => Storage::url($path)]);
        }

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('settings', 'public');
            GlobalSetting::updateOrCreate(['key' => 'favicon'], ['value' => Storage::url($path)]);
        }

        // Save Text Settings
        // Save Text Settings
        GlobalSetting::updateOrCreate(['key' => 'site_name'], ['value' => $validated['site_name']]);
        GlobalSetting::updateOrCreate(['key' => 'support_email'], ['value' => $validated['support_email']]);
        GlobalSetting::updateOrCreate(['key' => 'footer_text'], ['value' => $validated['footer_text']]);

        // Save Razorpay Settings (Optional)
        if ($request->has('razorpay_key')) {
            GlobalSetting::updateOrCreate(['key' => 'razorpay_key'], ['value' => $request->razorpay_key]);
        }
        if ($request->has('razorpay_secret')) {
             GlobalSetting::updateOrCreate(['key' => 'razorpay_secret'], ['value' => $request->razorpay_secret]);
        }

        return back()->with('success', 'Settings updated successfully');
    }
}
