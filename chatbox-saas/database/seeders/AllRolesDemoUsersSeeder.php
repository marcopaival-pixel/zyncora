<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AllRolesDemoUsersSeeder extends Seeder
{
    /**
     * Cria utilizadores para todos os perfis do sistema na Empresa Demo.
     * Senha padrão para todos: password
     */
    public function run(): void
    {
        $company = Company::where('slug', 'demo')->first();

        if (!$company) {
            $company = Company::create([
                'name' => 'Empresa Demo',
                'slug' => 'demo',
                'email' => 'contato@demo.local',
                'chat_color' => '#0ea5e9',
                'status' => 'active',
            ]);
        }

        $password = 'password'; // O modelo User usa cast hashed

        $users = [
            [
                'name' => 'Admin Empresa',
                'email' => 'admin@demo.local',
                'role' => User::ROLE_COMPANY_ADMIN,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Supervisor Demo',
                'email' => 'supervisor@demo.local',
                'role' => User::ROLE_SUPERVISOR,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Gerente Demo',
                'email' => 'gerente@demo.local',
                'role' => User::ROLE_MANAGER,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Financeiro Demo',
                'email' => 'financeiro@demo.local',
                'role' => User::ROLE_FINANCIAL,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Suporte Técnico Demo',
                'email' => 'suporte@demo.local',
                'role' => User::ROLE_TECHNICAL_SUPPORT,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Cliente Demo',
                'email' => 'cliente@demo.local',
                'role' => User::ROLE_CLIENT,
                'company_id' => $company->id,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => $password,
                    'status' => 'active',
                    'remember_token' => Str::random(10),
                ])
            );
        }

        $this->command->info('Utilizadores para todos os perfis criados com sucesso!');
        $this->command->info('Senha padrão: password');
    }
}
