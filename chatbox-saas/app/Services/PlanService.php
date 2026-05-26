<?php

namespace App\Services;

use App\Models\Company;

class PlanService
{
    /**
     * Verifica se a empresa atingiu o limite de usuários/atendentes.
     */
    public function canAddUser(Company $company): bool
    {
        return $company->users()->count() < $company->max_users;
    }

    /**
     * Verifica se a empresa atingiu o limite de atendentes (agentes).
     */
    public function canAddAttendant(Company $company): bool
    {
        $attendantsCount = $company->users()
            ->where('role', \App\Models\User::ROLE_AGENT)
            ->where('status', 'active')
            ->count();

        return $attendantsCount < $company->max_attendants;
    }

    /**
     * Verifica se a empresa atingiu o limite de canais.
     */
    public function canAddChannel(Company $company): bool
    {
        return $company->channels()->count() < $company->max_channels;
    }

    /**
     * Verifica se a empresa atingiu o limite de chatbots.
     */
    public function canAddChatbot(Company $company): bool
    {
        return $company->chatbots()->count() < $company->max_chatbots;
    }

    /**
     * Verifica se o plano da empresa está expirado.
     */
    public function isExpired(Company $company): bool
    {
        if (! $company->expires_at) {
            return false;
        }

        return now()->isAfter($company->expires_at);
    }

    /**
     * Slug normalizado para o mapa legado de funcionalidades (alinhado a PlanSeeder / Billing).
     */
    protected function resolvedPlanSlug(Company $company): string
    {
        $slug = $company->plan_id
            ? ($company->relationLoaded('plan') ? $company->plan?->slug : $company->plan()->value('slug'))
            : ($company->plan ?? 'basic');

        $slug = $slug ?: 'basic';

        return match ($slug) {
            'professional' => 'pro',
            default => $slug,
        };
    }

    /**
     * Define quais funcionalidades estão disponíveis por plano (string em companies.plan ou slug do Plan ligado).
     */
    public function hasFeature(Company $company, string $feature): bool
    {
        $plan = $this->resolvedPlanSlug($company);

        $features = config('chatbox.plans.features', []);

        return in_array($feature, $features[$plan] ?? [], true);
    }

    /**
     * Atalho para verificar uso de IA.
     */
    public function canUseAi(Company $company): bool
    {
        return $this->hasFeature($company, 'ai_automation');
    }
}
