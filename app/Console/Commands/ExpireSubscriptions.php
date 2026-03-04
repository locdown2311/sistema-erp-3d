<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';
    protected $description = 'Expira assinaturas vencidas e rebaixa o usuário para o plano gratuito';

    public function handle()
    {
        $freePlan = Plan::where('slug', 'free')->first();
        if (!$freePlan) {
            $this->error('Plano gratuito não encontrado!');
            return 1;
        }

        // Buscar assinaturas ativas com ends_at no passado
        $expired = Subscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->with('user', 'plan')
            ->get();

        if ($expired->isEmpty()) {
            $this->info('Nenhuma assinatura expirada.');
            return 0;
        }

        $count = 0;
        foreach ($expired as $sub) {
            // Marcar como expirada
            $sub->update(['status' => 'expired']);

            // Verificar se o usuário ainda tem outra assinatura ativa
            $hasActive = $sub->user->subscriptions()
                ->where('status', 'active')
                ->where('id', '!=', $sub->id)
                ->exists();

            if (!$hasActive) {
                // Criar assinatura gratuita
                $sub->user->subscriptions()->create([
                    'plan_id' => $freePlan->id,
                    'status' => 'active',
                    'starts_at' => now(),
                ]);
            }

            $count++;
            $this->line("  ⏬ {$sub->user->name} — {$sub->plan->name} expirado → Gratuito");
        }

        $this->info("✅ {$count} assinatura(s) expirada(s) e rebaixada(s).");
        return 0;
    }
}
