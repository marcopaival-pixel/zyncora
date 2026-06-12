<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::updateOrCreate(
            ['slug' => 'start'],
            [
                'name' => 'Start',
                'description' => 'Ideal para pequenos negócios validarem a solução.',
                'price' => 79.90,
                'max_users' => 1,
                'max_attendants' => 1,
                'max_channels' => 1,
                'max_chatbots' => 1,
                'max_ai_conversations' => 500,
                'features' => [
                    '1 Canal de atendimento',
                    '1 Atendente',
                    '1 Chatbot',
                    '500 Conversas IA/mês',
                    'FAQ',
                    'Widget para Site',
                    'Relatórios Básicos',
                ],
                'is_popular' => false,
                'is_active' => true,
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'professional'],
            [
                'name' => 'Professional',
                'description' => 'Para empresas que precisam de mais poder e múltiplos canais.',
                'price' => 199.90,
                'max_users' => 5,
                'max_attendants' => 5,
                'max_channels' => 3,
                'max_chatbots' => 3,
                'max_ai_conversations' => 3000,
                'features' => [
                    '3 Canais de atendimento',
                    '5 Atendentes',
                    '3 Chatbots',
                    '3.000 Conversas IA/mês',
                    'Base de Conhecimento',
                    'Fluxos Inteligentes',
                    'Relatórios Avançados',
                    'Upload de Documentos',
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
                'price' => 999.00,
                'max_users' => 20,
                'max_attendants' => 20,
                'max_channels' => 10,
                'max_chatbots' => 10,
                'max_ai_conversations' => 10000,
                'features' => [
                    '10 Canais de atendimento',
                    '20 Atendentes',
                    '10 Chatbots',
                    '10.000 Conversas IA/mês',
                    'Integrações via API',
                    'White Label',
                    'SLA garantido',
                    'Gerente de conta dedicado',
                    'Recursos Corporativos',
                ],
                'is_popular' => false,
                'is_active' => true,
            ]
        );
    }
}
