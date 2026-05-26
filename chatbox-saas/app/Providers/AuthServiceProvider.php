<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Conversation;
use App\Models\User;
use App\Policies\CompanyPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Conversation::class => ConversationPolicy::class,
        Company::class => CompanyPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
