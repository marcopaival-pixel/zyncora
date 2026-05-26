<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AgentDistributionService
{
    /**
     * Distribute waiting conversations to available agents for a company.
     * Optimized to avoid N+1 queries.
     */
    public function distribute(int $companyId): void
    {
        $waitingConversations = Conversation::where('company_id', $companyId)
            ->where('status', 'waiting')
            ->whereNull('assignee_id')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($waitingConversations->isEmpty()) {
            return;
        }

        // 1. Fetch all potential agents ONCE to avoid N+1
        $agents = User::where('company_id', $companyId)
            ->where('role', User::ROLE_AGENT)
            ->where('status', 'active')
            ->where('presence_status', 'online')
            ->with(['sectors']) // Eager load sectors
            ->withCount(['assignedConversations' => function ($q) {
                $q->where('status', 'open');
            }])
            ->get();

        if ($agents->isEmpty()) {
            return;
        }

        foreach ($waitingConversations as $conversation) {
            // 2. Find best agent from the collection in memory
            $targetAgent = $agents
                ->filter(function (User $agent) use ($conversation) {
                    // Check capacity
                    if ($agent->assigned_conversations_count >= ($agent->max_simultaneous_chats ?? 5)) {
                        return false;
                    }

                    // Check sector match if conversation has one
                    if ($conversation->sector_id) {
                        return $agent->sectors->contains('id', $conversation->sector_id);
                    }

                    return true;
                })
                ->sortBy('assigned_conversations_count')
                ->first();

            if ($targetAgent) {
                // 3. Update in memory count so the next conversation in the loop sees the updated load
                $targetAgent->assigned_conversations_count++;

                // 4. Persist change
                $conversation->update([
                    'assignee_id' => $targetAgent->id,
                    'status' => 'open',
                ]);

                Log::info("Conversation #{$conversation->id} automatically assigned to agent #{$targetAgent->id} ({$targetAgent->name})");
            }
        }
    }
}
