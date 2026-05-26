<?php

namespace App\Filament\Widgets\Concerns;

/**
 * Esconde o widget quando o utilizador não tem empresa associada (exceto admin de plataforma).
 */
trait RequiresCompanyOrPlatformAdmin
{
    public static function canView(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if ($user->isPlatformAdmin()) {
            return true;
        }

        return $user->company_id !== null;
    }
}
