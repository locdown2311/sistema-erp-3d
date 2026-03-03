<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Gratuito',
                'slug' => 'free',
                'price' => 0.00,
                'max_products' => 10,
                'max_sales_per_month' => 100,
                'max_wishlists' => 5,
                'can_export_reports' => false,
                'can_use_nfe' => false,
                'priority_store' => false,
                'can_customize_store' => false,
                'features' => [
                    'Até 10 produtos catalogados',
                    'Até 100 vendas por mês',
                    'Até 5 itens na Lista de Desejos',
                    'Loja no Marketplace',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Básico',
                'slug' => 'basic',
                'price' => 29.90,
                'max_products' => 50,
                'max_sales_per_month' => 300,
                'max_wishlists' => 25,
                'can_export_reports' => true,
                'can_use_nfe' => true,
                'priority_store' => false,
                'can_customize_store' => true,
                'features' => [
                    'Até 50 produtos catalogados',
                    'Até 300 vendas por mês',
                    'Até 25 itens na Lista de Desejos',
                    'Exportação de relatórios CSV/PDF',
                    'Notas Fiscais (NF-e)',
                    'Personalização de cores da loja',
                    'Suporte prioritário',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price' => 69.90,
                'max_products' => null,
                'max_sales_per_month' => null,
                'max_wishlists' => null,
                'can_export_reports' => true,
                'can_use_nfe' => true,
                'priority_store' => true,
                'can_customize_store' => true,
                'features' => [
                    'Produtos ILIMITADOS',
                    'Vendas ILIMITADAS',
                    'Lista de Desejos ILIMITADA',
                    'Exportação de relatórios CSV/PDF',
                    'Notas Fiscais (NF-e)',
                    'Personalização de cores da loja',
                    'Prioridade na vitrine do Marketplace',
                    'Suporte VIP',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            \App\Models\Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
