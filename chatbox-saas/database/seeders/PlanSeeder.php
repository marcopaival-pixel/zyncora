<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::updateOrCreate(
            ['slug' => 'basic'],
            [
                'name' => 'Básico',
                'description' => 'Ideal para pequenos negócios começando agora.',
                'price' => 49.90,
                'max_users' => 2,
                'max_attendants' => 1,
                'max_channels' => 1,
                'max_chatbots' => 1,
                'features' => [
                    'Suporte via email',
                    '1 Canal de atendimento',
                    '1 Atendente',
                    'Chatbot básico',
                ],
                'is_popular' => false,
                'is_active' => true,
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'professional'],
            [
                'name' => 'Profissional',
                'description' => 'Para empresas que precisam de mais poder e múltiplos canais.',
                'price' => 149.90,
                'max_users' => 10,
                'max_attendants' => 5,
                'max_channels' => 3,
                'max_chatbots' => 3,
                'features' => [
                    'Suporte prioritário',
                    '3 Canais de atendimento',
                    '5 Atendentes',
                    'Chatbots ilimitados',
                    'Relatórios avançados',
                ],
                'is_popular' => true,
                'is_active' => true,
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'description' => 'Solução completa para grandes operações.',
                'price' => 499.90,
                'max_users' => 50,
                'max_attendants' => 20,
                'max_channels' => 10,
                'max_chatbots' => 10,
                'features' => [
                    'Gerente de conta dedicado',
                    'Canais ilimitados',
                    'Usuários personalizados',
                    'Integrações via API',
                    'SLA garantido',
                ],
                'is_popular' => false,
                'is_active' => true,
            ]
        );
    }
}
