<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

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

        // Plano pago — Checkout Pro do Mercado Pago
        $accessToken = config('services.mercadopago.access_token');
        if (!$accessToken) {
            return redirect()->route('plans.index')
                ->with('error', 'Pagamento não configurado. Entre em contato com o suporte.');
        }

        try {
            MercadoPagoConfig::setAccessToken($accessToken);

            $client = new PreferenceClient();
            $preference = $client->create([
                'items' => [
                    [
                        'id' => "plan_{$plan->id}",
                        'title' => "Plano {$plan->name} - CENTRAL 3D",
                        'description' => "Assinatura mensal do plano {$plan->name}",
                        'quantity' => 1,
                        'unit_price' => (float) $plan->price,
                        'currency_id' => 'BRL',
                    ]
                ],
                'payer' => [
                    'email' => $user->email,
                    'name' => $user->name,
                ],
                'back_urls' => [
                    'success' => route('subscriptions.callback'),
                    'failure' => route('subscriptions.callback'),
                    'pending' => route('subscriptions.callback'),
                ],
                'auto_return' => 'approved',
                'external_reference' => json_encode([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                ]),
                'notification_url' => route('subscriptions.webhook'),
                'statement_descriptor' => 'CENTRAL3D',
            ]);

            return redirect($preference->init_point);
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            Log::error('Mercado Pago API error', [
                'status' => $e->getStatusCode(),
                'response' => $e->getApiResponse()?->getContent(),
                'message' => $e->getMessage(),
            ]);
            return redirect()->route('plans.index')
                ->with('error', 'Erro ao criar pagamento no Mercado Pago. Verifique os logs.');
        } catch (\Exception $e) {
            Log::error('Mercado Pago error: ' . $e->getMessage());
            return redirect()->route('plans.index')
                ->with('error', 'Erro ao conectar com o Mercado Pago. Tente novamente.');
        }
    }

    /**
     * Callback de retorno do Mercado Pago (back_url)
     */
    public function callback(Request $request)
    {
        $status = $request->query('status', $request->query('collection_status'));
        $paymentId = $request->query('payment_id', $request->query('collection_id'));
        $externalRef = $request->query('external_reference');

        if ($status === 'approved' && $externalRef) {
            $ref = json_decode($externalRef, true);
            $userId = $ref['user_id'] ?? null;
            $planId = $ref['plan_id'] ?? null;

            if ($userId && $planId && $userId == auth()->id()) {
                $plan = Plan::find($planId);
                if ($plan) {
                    $user = auth()->user();

                    // Verificar se já não foi ativado (evitar duplicata)
                    $existing = $user->subscriptions()
                        ->where('mp_payment_id', $paymentId)
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
                            'mp_payment_id' => $paymentId,
                            'mp_status' => 'approved',
                        ]);
                    }

                    return redirect()->route('plans.index')
                        ->with('success', "Pagamento aprovado! Plano {$plan->name} ativado com sucesso! 🎉");
                }
            }
        }

        if ($status === 'pending') {
            return redirect()->route('plans.index')
                ->with('warning', 'Seu pagamento está pendente de aprovação. O plano será ativado automaticamente após a confirmação.');
        }

        return redirect()->route('plans.index')
            ->with('error', 'O pagamento não foi aprovado. Tente novamente.');
    }

    /**
     * Webhook do Mercado Pago (notification_url)
     */
    public function webhook(Request $request)
    {
        Log::info('MP Webhook recebido', $request->all());

        if ($request->type !== 'payment' || !$request->has('data.id')) {
            return response()->json(['status' => 'ignored'], 200);
        }

        try {
            $accessToken = config('services.mercadopago.access_token');
            MercadoPagoConfig::setAccessToken($accessToken);

            $paymentClient = new \MercadoPago\Client\Payment\PaymentClient();
            $payment = $paymentClient->get((int) $request->input('data.id'));

            if (!$payment || !$payment->external_reference) {
                return response()->json(['status' => 'no_ref'], 200);
            }

            $ref = json_decode($payment->external_reference, true);
            $userId = $ref['user_id'] ?? null;
            $planId = $ref['plan_id'] ?? null;

            if (!$userId || !$planId) {
                return response()->json(['status' => 'invalid_ref'], 200);
            }

            $user = \App\Models\User::find($userId);
            $plan = Plan::find($planId);

            if (!$user || !$plan) {
                return response()->json(['status' => 'not_found'], 200);
            }

            if ($payment->status === 'approved') {
                // Evitar duplicatas
                $existing = $user->subscriptions()
                    ->where('mp_payment_id', (string) $payment->id)
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
                        'mp_payment_id' => (string) $payment->id,
                        'mp_status' => 'approved',
                    ]);
                }
            }

            return response()->json(['status' => 'ok'], 200);
        } catch (\Exception $e) {
            Log::error('MP Webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
}
