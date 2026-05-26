<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\PasswordReset\ResetPassword as BaseResetPassword;
use Illuminate\Contracts\Support\Htmlable;

class CustomResetPassword extends BaseResetPassword
{
    protected static string $view = 'filament.pages.auth.custom-reset-password';

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
