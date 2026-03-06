<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(): View
    {
        $settings = Setting::all()->keyBy('key');

        return view('settings.index', compact('settings'));
    }

    /**
     * Update the specified settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tax_igv_rate' => 'required|numeric|min:0|max:100',
            'tax_igv_name' => 'required|string|max:50',
            'company_name' => 'required|string|max:255',
            'company_ruc' => 'nullable|string|max:20',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email',
            'currency_symbol' => 'required|string|max:5',
            'currency_code' => 'required|string|max:3',
            'low_stock_threshold' => 'required|integer|min:1',
            'expiry_warning_days' => 'required|integer|min:1',
        ]);

        foreach ($validated as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                $type = match ($key) {
                    'tax_igv_rate', 'low_stock_threshold', 'expiry_warning_days' => 'number',
                    'company_email' => 'string',
                    default => $setting->type,
                };
                $setting->update(['value' => (string) $value, 'type' => $type]);
            }
        }

        return redirect()->route('settings.index')
            ->with('success', 'Configuración actualizada exitosamente');
    }
}
