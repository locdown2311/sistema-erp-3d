<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            $user->subscriptions()->where('status', 'active')->update(['status' => 'canceled', 'ends_at' => now()]);

            $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
            ]);

            return redirect()->route('plans.index')
                ->with('success', "Plano {$plan->name} ativado com sucesso!");
        }

        // Plano pago — Stripe Checkout
        $stripeKey = config('services.stripe.secret');
        if (!$stripeKey) {
            return redirect()->route('plans.index')
                ->with('error', 'Pagamento não configurado. Entre em contato com o suporte.');
        }

        try {
            \Stripe\Stripe::setApiKey($stripeKey);

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'brl',
                        'product_data' => [
                            'name' => "Plano {$plan->name} - Central 3D",
                            'description' => "Assinatura mensal do plano {$plan->name}",
                        ],
                        'unit_amount' => (int) ($plan->price * 100), // Stripe usa centavos
                    ],
                    'quantity' => 1,
                ]],
                'customer_email' => $user->email,
                'metadata' => [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                ],
                'success_url' => route('subscriptions.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('plans.index'),
            ]);

            return redirect($session->url);
        } catch (\Exception $e) {
            Log::error('Stripe error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            return redirect()->route('plans.index')
                ->with('error', 'Erro ao conectar com o Stripe. Tente novamente.');
        }
    }

    /**
     * Callback de sucesso do Stripe
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect()->route('plans.index')->with('error', 'Sessão de pagamento inválida.');
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return redirect()->route('plans.index')
                    ->with('error', 'O pagamento não foi confirmado. Tente novamente.');
            }

            $userId = $session->metadata->user_id;
            $planId = $session->metadata->plan_id;
            $user = auth()->user();

            // Verificar que o pagamento é do mesmo usuário logado
            if (!$user || $user->id != $userId) {
                return redirect()->route('plans.index')
                    ->with('error', 'Sessão de pagamento não corresponde ao usuário.');
            }

            $plan = Plan::find($planId);
            if (!$plan) {
                return redirect()->route('plans.index')
                    ->with('error', 'Plano não encontrado.');
            }

            // Evitar duplicatas
            $existing = $user->subscriptions()
                ->where('mp_payment_id', $session->payment_intent)
                ->where('status', 'active')
                ->first();

            if (!$existing) {
                $user->subscriptions()->where('status', 'active')
                    ->update(['status' => 'canceled', 'ends_at' => now()]);

                $user->subscriptions()->create([
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => now()->addMonth(),
                    'mp_payment_id' => $session->payment_intent,
                    'mp_status' => 'paid',
                ]);
            }

            return redirect()->route('plans.index')
                ->with('success', "Pagamento aprovado! Plano {$plan->name} ativado com sucesso! 🎉");
        } catch (\Exception $e) {
            Log::error('Stripe success callback error', ['message' => $e->getMessage()]);
            return redirect()->route('plans.index')
                ->with('error', 'Erro ao verificar pagamento. Entre em contato com o suporte.');
        }
    }

    /**
     * Webhook do Stripe
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            if ($webhookSecret) {
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } else {
                $event = json_decode($payload);
            }
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature error', ['message' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        $type = is_object($event) ? ($event->type ?? null) : null;

        if ($type === 'checkout.session.completed') {
            $session = $event->data->object;

            if ($session->payment_status === 'paid') {
                $userId = $session->metadata->user_id ?? null;
                $planId = $session->metadata->plan_id ?? null;

                if ($userId && $planId) {
                    $user = \App\Models\User::find($userId);
                    $plan = Plan::find($planId);

                    if ($user && $plan) {
                        $existing = $user->subscriptions()
                            ->where('mp_payment_id', $session->payment_intent)
                            ->where('status', 'active')
                            ->first();

                        if (!$existing) {
                            $user->subscriptions()->where('status', 'active')
                                ->update(['status' => 'canceled', 'ends_at' => now()]);

                            $user->subscriptions()->create([
                                'plan_id' => $plan->id,
                                'status' => 'active',
                                'starts_at' => now(),
                                'ends_at' => now()->addMonth(),
                                'mp_payment_id' => $session->payment_intent,
                                'mp_status' => 'paid',
                            ]);
                        }
                    }
                }
            }
        }

        return response('OK', 200);
    }
}
