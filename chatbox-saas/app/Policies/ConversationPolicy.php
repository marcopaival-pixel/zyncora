<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAndPermission;

class ConversationPolicy
{
    use ChecksTenantAndPermission;

    public function viewAny(User $user): bool
    {
        return $user->canChat();
    }

    public function view(User $user, Conversation $conversation): bool
    {
        return $this->sameCompany($user, $conversation) && $user->canChat();
    }

    public function update(User $user, Conversation $conversation): bool
    {
        return $this->sameCompany($user, $conversation)
            && $user->hasAnyPermission(['manage_conversas', 'view_conversas']);
    }
}
