<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'kwh_rate' => Setting::get('kwh_rate', '0.80'),
            'filament_price_kg' => Setting::get('filament_price_kg', '120.00'),
            'printer_wattage' => Setting::get('printer_wattage', '350'),
            'printer_price' => Setting::get('printer_price', '2500.00'),
            'printer_lifespan_hours' => Setting::get('printer_lifespan_hours', '5000'),
            'labor_rate' => Setting::get('labor_rate', '20.00'),
            'currency' => Setting::get('currency', 'BRL'),
            'company_name' => Setting::get('company_name', 'Minha Empresa 3D'),
        ];

        $user = auth()->user();

        return view('settings.index', compact('settings', 'user'));
    }

    public function update(Request $request)
    {
        $keys = [
            'kwh_rate', 'filament_price_kg', 'printer_wattage',
            'printer_price', 'printer_lifespan_hours', 'labor_rate',
            'currency', 'company_name',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key));
            }
        }

        // Save store colors directly on user
        $user = auth()->user();
        $user->update($request->only('store_color_primary', 'store_color_accent'));

        return redirect()->route('settings.index')
            ->with('success', 'Configurações salvas com sucesso!');
    }
}
