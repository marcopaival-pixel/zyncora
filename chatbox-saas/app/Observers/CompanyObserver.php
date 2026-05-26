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
