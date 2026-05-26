<?php

namespace App\Services;

use App\Models\Chatbot;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\ChatbotFlowExecution;
use Illuminate\Support\Facades\Log;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class FlowEngineService
{
    /**
     * Processa a entrada do usuário no contexto de um fluxo visual.
     */
    public function process(Conversation $conversation, ?string $userInput = null): ?Message
    {
        try {
            $conversation->loadMissing('channel');
            $chatbot = Chatbot::resolveForConversation($conversation);

            if (!$chatbot || !$chatbot->published_flow_data) {
                return null;
            }

            $flowData = $chatbot->published_flow_data['drawflow']['Home']['data'] ?? null;
            if (!$flowData) {
                return null;
            }

            // Recuperar ou criar execução do fluxo
            $execution = ChatbotFlowExecution::where('conversation_id', $conversation->id)
                ->where('is_completed', false)
                ->first();

            if (!$execution) {
                // Iniciar do "start"
                $startNode = collect($flowData)->firstWhere('name', 'start');
                if (!$startNode) {
                    Log::warning("Fluxo sem nó de 'start' para Empresa #{$conversation->company_id}");
                    return null;
                }

                $execution = ChatbotFlowExecution::create([
                    'company_id' => $conversation->company_id,
                    'conversation_id' => $conversation->id,
                    'current_node_id' => $startNode['id'],
                    'variables' => [],
                    'execution_log' => ['started_at' => now()->toIso8601String()],
                ]);

                return $this->executeNext($execution, $flowData, $conversation);
            }

            // Se o nó atual for de entrada (input), salva o que o usuário digitou
            $currentNode = $flowData[$execution->current_node_id] ?? null;
            if ($currentNode && $currentNode['name'] === 'input' && $userInput) {
                $variable = $currentNode['data']['params']['variable'] ?? null;
                if ($variable) {
                    $vars = $execution->variables ?? [];
                    $vars[$variable] = $userInput;
                    $execution->update(['variables' => $vars]);
                }
                
                return $this->executeNext($execution, $flowData, $conversation);
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Erro no Motor de Fluxo (Conversa #{$conversation->id}): " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    protected function executeNext($execution, $flowData, $conversation): ?Message
    {
        $currentNode = $flowData[$execution->current_node_id];
        
        // Encontrar conexão de saída (output_1)
        $connections = $currentNode['outputs']['output_1']['connections'] ?? [];
        if (empty($connections)) {
            $execution->update(['is_completed' => true]);
            return null;
        }

        $nextNodeId = $connections[0]['node'];
        $nextNode = $flowData[$nextNodeId] ?? null;

        if (!$nextNode) {
            $execution->update(['is_completed' => true]);
            return null;
        }

        $execution->update(['current_node_id' => $nextNodeId]);

        return $this->handleNode($execution, $nextNode, $flowData, $conversation);
    }

    protected function handleNode($execution, $node, $flowData, $conversation): ?Message
    {
        switch ($node['name']) {
            case 'message':
                $text = $this->replaceVariables($node['data']['params']['text'] ?? '', $execution->variables);
                $msg = $this->sendBotMessage($conversation, $text);
                
                // Nós de mensagem são passivos, pulam para o próximo automaticamente
                $this->executeNext($execution, $flowData, $conversation);
                return $msg;

            case 'input':
                $text = $this->replaceVariables($node['data']['params']['label'] ?? 'Qual sua resposta?', $execution->variables);
                return $this->sendBotMessage($conversation, $text);

            case 'buttons':
                $text = $this->replaceVariables($node['data']['params']['text'] ?? 'Escolha uma opção:', $execution->variables);
                return $this->sendBotMessage($conversation, $text); // To-do: Implementar botões reais no driver de saída

            case 'list':
                $params = $node['data']['params'] ?? [];
                $header = $this->replaceVariables($params['text'] ?? '', $execution->variables);
                $section = isset($params['section_title']) ? $this->replaceVariables((string) $params['section_title'], $execution->variables) : '';
                $body = $header !== '' ? $header : 'Escolha uma opção:';
                if ($section !== '') {
                    $body .= "\n\n".$section;
                }
                return $this->sendBotMessage($conversation, $body); // To-do: lista interativa no driver WhatsApp

            case 'condition':
                $params = $node['data']['params'] ?? [];
                $varName = trim((string) ($params['var'] ?? ''));
                $op = $params['op'] ?? 'filled';
                $val = (string) ($params['val'] ?? '');
                $vars = $execution->variables ?? [];
                $raw = $varName !== '' ? ($vars[$varName] ?? null) : null;
                if ($op === 'equals') {
                    $ok = trim((string) ($raw ?? '')) === trim($val);
                } else {
                    $ok = $raw !== null && trim((string) $raw) !== '';
                }
                $outKey = $ok ? 'output_1' : 'output_2';
                $connections = $node['outputs'][$outKey]['connections'] ?? [];
                if (empty($connections)) {
                    $execution->update(['is_completed' => true]);

                    return null;
                }
                $nextNodeId = $connections[0]['node'];
                $nextNode = $flowData[$nextNodeId] ?? null;
                if (! $nextNode) {
                    $execution->update(['is_completed' => true]);

                    return null;
                }
                $execution->update(['current_node_id' => $nextNodeId]);

                return $this->handleNode($execution, $nextNode, $flowData, $conversation);

            case 'action':
                $type = $node['data']['params']['type'] ?? null;
                if ($type === 'transfer') {
                    $conversation->update([
                        'assignee_id' => null,
                        'status' => 'waiting',
                        'sector_id' => $node['data']['params']['sector_id'] ?? $conversation->sector_id,
                    ]);
                    
                    // Trigger distribution
                    app(AgentDistributionService::class)->distribute($conversation->company_id);
                }
                return $this->executeNext($execution, $flowData, $conversation);

            case 'end':
                $execution->update(['is_completed' => true]);
                return $this->sendBotMessage($conversation, "Atendimento encerrado automaticamente pelo sistema.");

            case 'wait':
                // Nota: Wait em tempo real precisaria de um Job agendado, 
                // para este MVP simplificado vamos apenas pular
                return $this->executeNext($execution, $flowData, $conversation);

            case 'http_request':
                $params = $node['data']['params'] ?? [];
                $url = $this->replaceVariables($params['url'] ?? '', $execution->variables ?? []);
                $method = strtoupper($params['method'] ?? 'GET');
                $storeIn = trim((string) ($params['store_in'] ?? 'api_response'));
                
                if ($url !== '') {
                    try {
                        $response = \Illuminate\Support\Facades\Http::timeout(10)->send($method, $url);
                        $vars = $execution->variables ?? [];
                        
                        if ($response->successful()) {
                            $vars[$storeIn] = $response->json() ?? $response->body();
                        } else {
                            $vars[$storeIn] = ['error' => true, 'status' => $response->status()];
                        }
                        
                        $execution->update(['variables' => $vars]);
                    } catch (\Exception $e) {
                        Log::error("Erro no n de HTTP_REQUEST (FlowBuilder): " . $e->getMessage());
                    }
                }
                return $this->executeNext($execution, $flowData, $conversation);

            case 'calendar':
                $text = $this->replaceVariables($node['data']['params']['text'] ?? 'Selecione o melhor horário para nossa reunião:', $execution->variables);
                return $conversation->messages()->create([
                    'sender_type' => 'bot',
                    'body' => $text,
                    'message_type' => 'calendar',
                    'sent_at' => now(),
                ]);

            case 'expression':
                // Sandbox de execuǜo de lgica segura (Code Node)
                $params = $node['data']['params'] ?? [];
                $script = trim((string) ($params['script'] ?? ''));
                $storeIn = trim((string) ($params['store_in'] ?? ''));
                
                if ($script !== '' && $storeIn !== '') {
                    try {
                        $el = new ExpressionLanguage();
                        $result = $el->evaluate($script, $execution->variables ?? []);
                        
                        $vars = $execution->variables ?? [];
                        $vars[$storeIn] = $result;
                        $execution->update(['variables' => $vars]);
                    } catch (\Exception $e) {
                        Log::error("Erro no n de Expression (FlowBuilder): " . $e->getMessage());
                    }
                }
                return $this->executeNext($execution, $flowData, $conversation);

            default:
                Log::debug("Nó desconhecido no fluxo: " . $node['name']);
                return null;
        }
    }

    protected function replaceVariables(string $text, array $variables): string
    {
        return preg_replace_callback('/@{{(.*?)}}/', function ($matches) use ($variables) {
            $key = trim($matches[1]);
            return $variables[$key] ?? $matches[0];
        }, $text);
    }

    protected function sendBotMessage(Conversation $conversation, string $body): Message
    {
        return $conversation->messages()->create([
            'sender_type' => 'bot',
            'body' => $body,
            'message_type' => 'text',
            'sent_at' => now(),
        ]);
    }
}
