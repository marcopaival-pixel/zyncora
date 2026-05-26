<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RBACSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'Dashboard' => ['view'],
            'Conversas' => ['view', 'manage', 'delete'],
            'Clientes' => ['view', 'create', 'edit', 'delete'],
            'Chatbot' => ['view', 'manage', 'configure'],
            'Filas' => ['view', 'manage'],
            'Usuários' => ['view', 'create', 'edit', 'delete'],
            'Perfis' => ['view', 'manage'],
            'Canais' => ['view', 'manage'],
            'Relatórios' => ['view', 'export'],
            'Financeiro' => ['view', 'manage'],
            'Integrações' => ['view', 'manage'],
            'Configurações' => ['view', 'manage'],
            'Logs' => ['view'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $name = strtolower($action.'_'.str_replace([' ', '/', '(', ')', ','], '_', $module));
                Permission::firstOrCreate(
                    ['name' => $name],
                    [
                        'module' => $module,
                        'description' => ucfirst($action).' access for '.$module.' module',
                    ]
                );
            }
        }

        $allPermissions = Permission::all();

        $roles = [
            [
                'slug' => 'company_admin',
                'name' => 'Admin Empresa',
                'description' => 'Administrador da empresa (tenant).',
                'modules' => ['Dashboard', 'Conversas', 'Clientes', 'Chatbot', 'Filas', 'Usuários', 'Perfis', 'Relatórios', 'Financeiro', 'Integrações', 'Configurações', 'Logs'],
            ],
            [
                'slug' => User::ROLE_SUPERVISOR,
                'name' => 'Supervisor',
                'description' => 'Team Supervisor.',
                'modules' => ['Dashboard', 'Conversas', 'Clientes', 'Chatbot', 'Filas', 'Relatórios', 'Logs'],
            ],
            [
                'slug' => User::ROLE_AGENT,
                'name' => 'Atendente',
                'description' => 'Support Agent.',
                'modules' => ['Dashboard', 'Conversas', 'Clientes'],
            ],
            [
                'slug' => User::ROLE_MANAGER,
                'name' => 'Gestor',
                'description' => 'Company Manager.',
                'modules' => ['Dashboard', 'Conversas', 'Clientes', 'Chatbot', 'Filas', 'Usuários', 'Relatórios', 'Financeiro', 'Integrações', 'Configurações'],
            ],
            [
                'slug' => User::ROLE_FINANCIAL,
                'name' => 'Financeiro',
                'description' => 'Financial Department.',
                'modules' => ['Dashboard', 'Relatórios', 'Financeiro'],
            ],
            [
                'slug' => User::ROLE_TECHNICAL_SUPPORT,
                'name' => 'Suporte Técnico',
                'description' => 'Technical Support.',
                'modules' => ['Dashboard', 'Canais', 'Integrações', 'Configurações', 'Logs'],
            ],
            [
                'slug' => User::ROLE_CLIENT,
                'name' => 'Cliente',
                'description' => 'End Customer.',
                'modules' => ['Conversas'],
                'permission_names' => ['view_conversas'],
            ],
        ];

        foreach ($roles as $roleConfig) {
            $role = Role::updateOrCreate(
                ['slug' => $roleConfig['slug']],
                [
                    'name' => $roleConfig['name'],
                    'description' => $roleConfig['description'],
                ]
            );

            if (isset($roleConfig['permission_names'])) {
                $permissions = Permission::whereIn('name', $roleConfig['permission_names'])->get();
            } else {
                $permissions = Permission::whereIn('module', $roleConfig['modules'])->get();
            }

            $role->permissions()->sync($permissions->pluck('id'));
        }

        // Legado: role "Administrador" sem slug (referência UI)
        $legacyAdmin = Role::firstOrCreate(
            ['name' => 'Administrador'],
            ['description' => 'System Administrator with full access.', 'slug' => null]
        );
        $legacyAdmin->permissions()->sync($allPermissions->pluck('id'));
    }
}
