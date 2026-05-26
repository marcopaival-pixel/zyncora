<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Utilizadores e empresa de demonstração (admin da plataforma + atendente).
 * Palavra-passe em texto plano: o modelo {@see User} usa cast "hashed".
 */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->firstOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Empresa Demo',
                'email' => 'contato@demo.local',
                'chat_color' => '#0ea5e9',
                'welcome_message' => 'Olá! Bem-vindo ao atendimento.',
                'business_hours' => [
                    'monday' => ['start' => '09:00', 'end' => '18:00'],
                    'tuesday' => ['start' => '09:00', 'end' => '18:00'],
                    'wednesday' => ['start' => '09:00', 'end' => '18:00'],
                    'thursday' => ['start' => '09:00', 'end' => '18:00'],
                    'friday' => ['start' => '09:00', 'end' => '18:00'],
                ],
                'status' => 'active',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador da plataforma',
                'password' => 'password',
                'role' => User::ROLE_PLATFORM_ADMIN,
                'company_id' => null,
                'status' => 'active',
                'remember_token' => Str::random(10),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'agente@demo.local'],
            [
                'name' => 'Atendente Demo',
                'password' => 'password',
                'role' => User::ROLE_AGENT,
                'company_id' => $company->id,
                'status' => 'active',
                'remember_token' => Str::random(10),
            ]
        );
    }
}
