<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Get current user's settings
     */
    public function show(Request $request)
    {
        $setting = Setting::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'dark_mode' => false,
                'notifications' => true,
                'language' => 'Khmer',
            ]
        );

        return response()->json([
            'status' => 'success',
            'data' => $setting,
        ]);
    }

    /**
     * Update current user's settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'dark_mode' => ['sometimes', 'boolean'],
            'notifications' => ['sometimes', 'boolean'],
            'language' => ['sometimes', 'in:Khmer,English'],
        ]);

        $setting = Setting::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'dark_mode' => false,
                'notifications' => true,
                'language' => 'Khmer',
            ]
        );

        $setting->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Settings updated successfully',
            'data' => $setting->fresh(),
        ]);
    }
}