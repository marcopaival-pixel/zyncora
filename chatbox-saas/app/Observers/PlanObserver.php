<?php

namespace App\Observers;

use App\Models\Plan;
use App\Models\PlanAuditLog;
use Illuminate\Support\Facades\Cache;

class PlanObserver
{
    /**
     * Handle the Plan "created" event.
     */
    public function created(Plan $plan): void
    {
        Cache::forget('public_plans');
    }

    /**
     * Handle the Plan "updated" event.
     */
    public function updated(Plan $plan): void
    {
        Cache::forget('public_plans');

        if ($plan->isDirty()) {
            PlanAuditLog::create([
                'plan_id' => $plan->id,
                'user_id' => auth()->id(),
                'old_values' => $plan->getOriginal(),
                'new_values' => $plan->getChanges(),
            ]);
        }
    }

    /**
     * Handle the Plan "deleted" event.
     */
    public function deleted(Plan $plan): void
    {
        Cache::forget('public_plans');
    }

    /**
     * Handle the Plan "restored" event.
     */
    public function restored(Plan $plan): void
    {
        Cache::forget('public_plans');
    }

    /**
     * Handle the Plan "force deleted" event.
     */
    public function forceDeleted(Plan $plan): void
    {
        Cache::forget('public_plans');
    }
}
