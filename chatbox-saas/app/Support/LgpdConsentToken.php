<?php

namespace App\Support;

use App\Models\Company;

class LgpdConsentToken
{
    public static function make(Company $company): string
    {
        return hash_hmac('sha256', self::payload($company), self::key());
    }

    public static function isValid(Company $company, ?string $token): bool
    {
        if (! is_string($token) || $token === '') {
            return false;
        }

        return hash_equals(self::make($company), $token);
    }

    private static function payload(Company $company): string
    {
        return "{$company->id}|{$company->slug}";
    }

    private static function key(): string
    {
        return (string) config('app.key');
    }
}
