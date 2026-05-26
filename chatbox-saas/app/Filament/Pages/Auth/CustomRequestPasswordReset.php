<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Illuminate\Contracts\Support\Htmlable;

class CustomRequestPasswordReset extends BaseRequestPasswordReset
{
    protected static string $view = 'filament.pages.auth.custom-request-password-reset';

    public function hasLogo(): bool
    {
        return false;
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return null;
    }
}
