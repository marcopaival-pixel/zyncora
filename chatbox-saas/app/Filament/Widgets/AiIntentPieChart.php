<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\AiAnswerAuditLog;

class AiIntentPieChart extends ChartWidget
{
    protected static ?string $heading = 'Resolução de Conversas (IA vs Base de Dados)';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $company = auth()->user()->company;

        if (!$company) {
            return [];
        }

        // Como o Audit Log não tem company_id diretamente, precisamos buscar através das conversations
        // Para simplificar no MVP, vamos fazer um mock visual baseado na tabela de AuditLog
        // Idealmente, AiAnswerAuditLog deveria ter company_id
        
        $stats = \DB::table('ai_answer_audit_logs')
            ->join('conversations', 'ai_answer_audit_logs.conversation_id', '=', 'conversations.id')
            ->where('conversations.company_id', $company->id)
            ->select('source_used', \DB::raw('count(*) as total'))
            ->groupBy('source_used')
            ->pluck('total', 'source_used')
            ->toArray();

        $labels = [];
        $data = [];
        $colors = [];

        // Fontes: 'company_data', 'faq', 'external_api', 'generative_ai' (default)
        if (isset($stats['company_data'])) {
            $labels[] = 'Dados da Empresa';
            $data[] = $stats['company_data'];
            $colors[] = '#10b981'; // emerald
        }
        
        if (isset($stats['faq'])) {
            $labels[] = 'FAQ (Custo Zero)';
            $data[] = $stats['faq'];
            $colors[] = '#3b82f6'; // blue
        }

        if (isset($stats['external_api'])) {
            $labels[] = 'API Externa';
            $data[] = $stats['external_api'];
            $colors[] = '#8b5cf6'; // violet
        }

        // O resto seria o modelo generativo, vamos simular o uso total subtraindo os "salvos"
        $totalConversations = $company->ai_conversations_used;
        $saved = array_sum($data);
        $generative = max(0, $totalConversations - $saved);

        if ($generative > 0 || empty($data)) {
            $labels[] = 'IA Generativa (Gemini)';
            $data[] = $generative;
            $colors[] = '#f59e0b'; // amber
        }

        return [
            'datasets' => [
                [
                    'label' => 'Resoluções',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
