<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = auth()->user();
        $plan = Plan::findOrFail($request->plan_id);

        // Se é o plano gratuito, ativar direto
        if ($plan->price <= 0) {
            // Cancelar assinatura anterior se existir
            $user->subscriptions()->where('status', 'active')->update(['status' => 'canceled', 'ends_at' => now()]);

            $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
            ]);

            return redirect()->route('plans.index')
                ->with('success', "Plano {$plan->name} ativado com sucesso!");
        }

        // Para planos pagos, por enquanto apenas informar que está em desenvolvimento
        // Futuramente: integração com Mercado Pago
        return redirect()->route('plans.index')
            ->with('error', 'Pagamentos online estão em desenvolvimento. Entre em contato para ativar planos pagos.');
    }
}
