<?php

namespace App\Observers;

use App\Models\Company;

class CompanyObserver
{
    /**
     * Handle the Company "created" event.
     */
    public function created(Company $company): void
    {
        //
    }

    /**
     * Handle the Company "updated" event.
     */
    public function updated(Company $company): void
    {
        \Illuminate\Support\Facades\Cache::forget("widget_config_{$company->slug}");
        
        // Se o slug mudou (raro, mas possível), limpa o antigo também
        if ($company->wasChanged('slug')) {
            \Illuminate\Support\Facades\Cache::forget("widget_config_{$company->getOriginal('slug')}");
        }

        if ($company->wasChanged(['plan_id', 'status', 'expires_at'])) {
            \App\Models\SubscriptionAuditLog::create([
                'company_id' => $company->id,
                'action' => 'admin_update',
                'old_status' => $company->getOriginal('status'),
                'new_status' => $company->status,
                'notes' => 'Plano/Status/Vencimento alterado via Painel Admin por: ' . (auth()->user()?->name ?? 'Sistema'),
            ]);
        }
    }

    /**
     * Handle the Company "deleted" event.
     */
    public function deleted(Company $company): void
    {
        \Illuminate\Support\Facades\Cache::forget("widget_config_{$company->slug}");
    }

    /**
     * Handle the Company "restored" event.
     */
    public function restored(Company $company): void
    {
        \Illuminate\Support\Facades\Cache::forget("widget_config_{$company->slug}");
    }

    /**
     * Handle the Company "force deleted" event.
     */
    public function forceDeleted(Company $company): void
    {
        \Illuminate\Support\Facades\Cache::forget("widget_config_{$company->slug}");
    }
}
