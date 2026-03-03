<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'nfe_ambiente' => Setting::get('nfe_ambiente', '2'),
            'nfe_uf' => Setting::get('nfe_uf', 'SP'),
            'nfe_emit_nome' => Setting::get('nfe_emit_nome'),
            'nfe_emit_cnpj' => Setting::get('nfe_emit_cnpj'),
            'nfe_emit_ie' => Setting::get('nfe_emit_ie'),
            'nfe_emit_cep' => Setting::get('nfe_emit_cep'),
            'nfe_emit_logradouro' => Setting::get('nfe_emit_logradouro'),
            'nfe_emit_numero' => Setting::get('nfe_emit_numero'),
            'nfe_emit_bairro' => Setting::get('nfe_emit_bairro'),
            'nfe_emit_municipio' => Setting::get('nfe_emit_municipio'),
            'nfe_emit_uf' => Setting::get('nfe_emit_uf'),
            'nfe_certificado_path' => Setting::get('nfe_certificado_path'),
            'nfe_certificado_senha' => Setting::get('nfe_certificado_senha') ? '******' : null,
        ];

        $user = auth()->user();

        return view('settings.index', compact('settings', 'user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $keys = [
            'kwh_rate', 'filament_price_kg', 'printer_wattage',
            'printer_price', 'printer_lifespan_hours', 'labor_rate',
            'currency', 'company_name',
            'contact_email', 'contact_phone', 'address', 'invoice_notes',
            'nfe_ambiente', 'nfe_uf', 
            'nfe_emit_nome', 'nfe_emit_cnpj', 'nfe_emit_ie',
            'nfe_emit_cep', 'nfe_emit_logradouro', 'nfe_emit_numero',
            'nfe_emit_bairro', 'nfe_emit_municipio', 'nfe_emit_uf'
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key));
            }
        }
        
        // Handle Certificate password (only update if provided)
        if ($request->filled('nfe_certificado_senha')) {
            Setting::set('nfe_certificado_senha', encrypt($request->input('nfe_certificado_senha')));
        }
        
        // Handle Certificate Upload
        if ($request->hasFile('nfe_certificado')) {
            $request->validate([
                'nfe_certificado' => 'file|max:2048|mimetypes:application/x-pkcs12,application/octet-stream',
            ]);
            
            $file = $request->file('nfe_certificado');
            // Check extension specifically as mimetypes for pfx can be tricky
            $extension = strtolower($file->getClientOriginalExtension());
            if (in_array($extension, ['pfx', 'p12'])) {
                // Save privately - outside of public disk
                $path = $file->storeAs(
                    'certificates', 
                    $user->id . '_certificado.' . $extension, 
                    'local' // local disk is usually private (storage/app/)
                );
                
                Setting::set('nfe_certificado_path', $path);
            }
        }

        // Save store colors directly on user
        $userData = $request->only('store_color_primary', 'store_color_accent');

        if ($request->hasFile('store_logo')) {
            $request->validate([
                'store_logo' => 'image|max:2048', // 2MB max
            ]);

            // Delete old logo if it's a local file
            if ($user->store_logo && !str_starts_with($user->store_logo, 'http') && Storage::disk('public')->exists($user->store_logo)) {
                Storage::disk('public')->delete($user->store_logo);
            }

            // Save new logo to local public storage
            $logoPath = $request->file('store_logo')->store('logos', 'public');
            $userData['store_logo'] = $logoPath;
        }

        $user->update($userData);

        return redirect()->route('settings.index')
            ->with('success', 'Configurações salvas com sucesso!');
    }
}
