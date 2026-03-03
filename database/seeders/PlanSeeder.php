<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Gratuito',
                'slug' => 'free',
                'price' => 0.00,
                'max_products' => 10,
                'features' => [
                    'Até 10 produtos catalogados',
                    'Acesso básico ao ERP',
                    'Venda no Marketplace'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Básico',
                'slug' => 'basic',
                'price' => 29.90,
                'max_products' => 50,
                'features' => [
                    'Até 50 produtos catalogados',
                    'Acesso completo ao ERP',
                    'Prioridade de suporte'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price' => 69.90,
                'max_products' => null, // Ilimitado
                'features' => [
                    'Produtos ILIMITADOS',
                    'Gestão avançada de estoque',
                    'Relatórios Premium',
                    'Prioridade na vitrine'
                ],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            \App\Models\Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
