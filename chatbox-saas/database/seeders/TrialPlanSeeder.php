<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class TrialPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::firstOrCreate([
            'slug' => 'trial',
        ], [
            'name' => 'Trial de 14 Dias',
            'description' => 'Plano de teste gratuito da Zynkora.',
            'price' => 0.00,
            'interval' => 'month',
            'max_users' => 1,
            'max_channels' => 1,
            'max_chatbots' => 3,
            'max_ai_conversations' => 500,
            'features' => [
                'messages_limit' => 1000,
                'has_api' => false,
                'has_white_label' => false,
            ],
            'is_active' => true,
        ]);
    }
}
