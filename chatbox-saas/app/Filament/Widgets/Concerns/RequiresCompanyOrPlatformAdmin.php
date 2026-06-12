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

        // Esconder do Platform Admin na visão global (mostra só quando está impersonando)
        if ($user->isPlatformAdmin()) {
            return false;
        }

        return $user->company_id !== null;
    }
}
