<?php

namespace App\Services;

use App\Models\Chatbot;
use App\Models\ChatbotFlow;
use App\Models\ChatbotFlowExecution;
use App\Models\Company;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\Chatbot\MessageOrchestratorService;

class ChatbotReplyService
{
    public function __construct(
        protected AIService $aiService,
        protected PlanService $planService,
        protected FlowEngineService $flowEngine,
        protected AgentDistributionService $distributionService,
        protected MessageOrchestratorService $orchestrator
    ) {}

    public function maybeAutoReply(Conversation $conversation, string $inboundText): ?Message
    {
        $company = $conversation->company;

        if ($company) {
            app(TenantService::class)->setCompany($company);
        }

        if (! $company->auto_reply_enabled) {
            return null;
        }

        if ($conversation->assignee_id !== null) {
            return null;
        }

        // Executar a análise de Lead/Oportunidade ANTES de responder (CRM - Fase 1)
        $this->aiService->analyzeConversation($conversation);

        $text = mb_strtolower(trim($inboundText));

        // 0. Transbordo Inteligente por Sentimento (Sentiment Analysis / PNL Lite)
        if ($this->detectNegativeSentiment($text)) {
            $this->pushBotMessage($conversation, 'Notei que você está frustrado. Estou transferindo sua conversa para a nossa equipe gerencial humana agora mesmo para resolver isso.');
            $this->markAsWaitingAndDistribute($conversation);

            return null; // Stop AI/Bot processing
        }

        // 1. PRIORIDADE: Motor de Fluxo Visual Profissional
        $flowResponse = $this->flowEngine->process($conversation, $inboundText);
        if ($flowResponse) {
            return $flowResponse;
        }

        // 2. Gatilhos por Palavra-Chave (Configuração simples)
        $flows = $company->chatbotFlows()
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($flows as $flow) {
            $trigger = $flow->trigger ? mb_strtolower($flow->trigger) : null;
            if ($trigger && $trigger !== '' && str_contains($text, $trigger)) {
                return $this->runKeywordFlowChain($conversation, $flow);
            }
        }

        // 3. INTELLIGENT AI (Knowledge Base)
        $conversation->loadMissing('channel');
        $chatbot = Chatbot::resolveForConversation($conversation, function ($q) {
            $q->where('use_ai', true);
        });

        if ($chatbot) {
            if (! $this->planService->canUseAi($company)) {
                $this->markAsWaitingAndDistribute($conversation);

                return null;
            }

            // Verifica saldo de créditos de IA
            if ($company->ai_credits_balance <= 0) {
                $this->pushBotMessage($conversation, 'Nossos assistentes virtuais estão temporariamente indisponíveis. Vou transferir você para nossa equipe humana.');
                $this->markAsWaitingAndDistribute($conversation);

                return null;
            }

            // Usar o Orquestrador Central para gerenciar intenção e agentes
            $aiResponse = $this->orchestrator->handleIncomingMessage($conversation, $chatbot, $inboundText);

            return $this->pushBotMessage($conversation, $aiResponse);
        }

        $this->markAsWaitingAndDistribute($conversation);

        return null;
    }

    protected function markAsWaitingAndDistribute(Conversation $conversation): void
    {
        $conversation->update(['status' => 'waiting', 'assignee_id' => null]);
        $this->distributionService->distribute($conversation->company_id);
    }

    protected function detectNegativeSentiment(string $text): bool
    {
        // PNL Lite: Detecção rápida de intenção negativa / agressiva
        $angryKeywords = ['absurdo', 'lixo', 'procon', 'processar', 'advogado', 'reclame aqui', 'pessimo', 'péssimo', 'horrivel', 'horrível', 'ódio', 'cancelar', 'reembolso'];

        foreach ($angryKeywords as $word) {
            if (str_contains($text, $word)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Executa o fluxo por palavra-chave e encadeamentos via {@see ChatbotFlow::$next_flow_key}
     * (correspondência ao campo {@see ChatbotFlow::$trigger} de outro registo, sem diferenciar maiúsculas).
     *
     * @return Message|null Primeira mensagem do bot na cadeia (compatível com o retorno de maybeAutoReply).
     */
    protected function runKeywordFlowChain(Conversation $conversation, ChatbotFlow $primary): ?Message
    {
        $firstMessage = null;
        $current = $primary;
        $visited = [];
        $depth = 0;

        while ($current !== null && $depth < 8) {
            if (isset($visited[$current->id])) {
                break;
            }
            $visited[$current->id] = true;

            $message = $this->pushBotMessage($conversation, (string) $current->answer);
            if ($firstMessage === null) {
                $firstMessage = $message;
            }

            $this->applyKeywordFlowAction($conversation, $current->fresh());

            $nextKey = trim((string) $current->next_flow_key);
            if ($nextKey === '') {
                break;
            }

            $conversation->refresh();
            if ($conversation->status === 'closed') {
                break;
            }

            $current = $this->findKeywordFlowByTriggerKey($conversation->company_id, $nextKey);
            $depth++;
        }

        return $firstMessage;
    }

    protected function findKeywordFlowByTriggerKey(int $companyId, string $key): ?ChatbotFlow
    {
        $normalized = mb_strtolower(trim($key));
        if ($normalized === '') {
            return null;
        }

        return ChatbotFlow::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('active', true)
            ->whereNotNull('trigger')
            ->get()
            ->first(function (ChatbotFlow $flow) use ($normalized): bool {
                return mb_strtolower(trim((string) $flow->trigger)) === $normalized;
            });
    }

    /**
     * Efeitos do campo "ação interna" em fluxos por palavra-chave (alinhado ao formulário Filament).
     */
    protected function applyKeywordFlowAction(Conversation $conversation, ChatbotFlow $flow): void
    {
        $action = $flow->action;
        if ($action === null || $action === '') {
            return;
        }

        if ($action === 'transfer') {
            $conversation->update([
                'assignee_id' => null,
                'status' => 'waiting',
            ]);
            $this->distributionService->distribute($conversation->company_id);

            return;
        }

        if ($action === 'end') {
            $conversation->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            return;
        }

        if ($action === 'menu') {
            ChatbotFlowExecution::query()
                ->where('conversation_id', $conversation->id)
                ->where('is_completed', false)
                ->delete();
        }
    }

    public function offlineNoticeIfClosed(Company $company): ?string
    {
        if ($this->isWithinBusinessHours($company)) {
            return null;
        }

        return $company->offline_message;
    }

    public function isWithinBusinessHours(Company $company): bool
    {
        $hours = $company->business_hours;
        if (! is_array($hours) || $hours === []) {
            return true;
        }

        $now = now(config('app.timezone'))->locale('en');
        $day = mb_strtolower((string) $now->format('l'));

        if (! isset($hours[$day]) || ! is_array($hours[$day])) {
            return true;
        }

        $start = $hours[$day]['start'] ?? null;
        $end = $hours[$day]['end'] ?? null;
        if (! $start || ! $end) {
            return true;
        }

        $t = $now->format('H:i');

        return $t >= $start && $t <= $end;
    }

    protected function pushBotMessage(Conversation $conversation, string $body): Message
    {
        return $conversation->messages()->create([
            'sender_type' => 'bot',
            'sender_id' => null,
            'body' => $body,
            'message_type' => 'text',
            'sent_at' => now(),
        ]);
    }
}
