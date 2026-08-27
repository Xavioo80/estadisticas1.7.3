<?php

namespace App\Http\Controllers;

use App\Models\LoginSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LoginSettingsController extends Controller
{
    public function index()
    {
        $settings = LoginSetting::first() ?? new LoginSetting();
        return view('admin.settings.login', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = LoginSetting::first();
        if (!$settings) {
            $settings = new LoginSetting();
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'primary_color' => 'required|string|size:7',
            'secondary_color' => 'required|string|size:7',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'background' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        $settings->title = $request->title;
        $settings->primary_color = $request->primary_color;
        $settings->secondary_color = $request->secondary_color;

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $path = $request->file('logo')->store('branding', 'public');
            $settings->logo_path = $path;
        }

        if ($request->hasFile('background')) {
            // Delete old background
            if ($settings->background_image_path) {
                Storage::disk('public')->delete($settings->background_image_path);
            }
            $path = $request->file('background')->store('branding', 'public');
            $settings->background_image_path = $path;
        }

        $settings->save();

        return redirect()->route('admin.settings.login')->with('success', 'Configuración de acceso actualizada correctamente.');
    }
}
