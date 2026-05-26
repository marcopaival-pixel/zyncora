<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function ($user, string $conversationId) {
    $conversation = Conversation::query()->find($conversationId);
    if (! $conversation) {
        return false;
    }

    if (method_exists($user, 'isPlatformAdmin') && $user->isPlatformAdmin()) {
        return ['id' => $user->id, 'name' => $user->name];
    }

    if ((int) $user->company_id !== (int) $conversation->company_id) {
        return false;
    }

    return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('company.{companyId}', function ($user, string $companyId) {
    if (method_exists($user, 'isPlatformAdmin') && $user->isPlatformAdmin()) {
        return true;
    }

    return (int) $user->company_id === (int) $companyId;
});
