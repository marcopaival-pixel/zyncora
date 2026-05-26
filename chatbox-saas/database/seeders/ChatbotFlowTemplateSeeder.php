<?php

namespace Database\Seeders;

use App\Models\ChatbotFlowTemplate;
use Illuminate\Database\Seeder;

class ChatbotFlowTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => '🚀 Saudação e Triagem',
                'description' => 'Boas-vindas profissional com menu de opções para encaminhamento.',
                'category' => 'Atendimento',
                'is_public' => true,
                'flow_data' => [
                    'drawflow' => [
                        'Home' => [
                            'data' => [
                                '1' => ['id' => 1, 'name' => 'start', 'data' => ['params' => []], 'class' => 'node-start', 'html' => '', 'inputs' => [], 'outputs' => ['output_1' => ['connections' => [['node' => '2', 'output' => 'input_1']]]], 'pos_x' => 50, 'pos_y' => 200],
                                '2' => ['id' => 2, 'name' => 'message', 'data' => ['params' => ['text' => 'Olá! Bem-vindo à nossa central de atendimento dinâmica. Como posso te ajudar hoje?']], 'class' => 'node-message', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '1', 'output' => 'output_1']]]], 'outputs' => ['output_1' => ['connections' => [['node' => '3', 'output' => 'input_1']]]], 'pos_x' => 350, 'pos_y' => 200],
                                '3' => ['id' => 3, 'name' => 'buttons', 'data' => ['params' => ['text' => 'Escolha uma das opções abaixo para agilizar seu contato:', 'btn1' => 'Comercial', 'btn2' => 'Suporte Técnico', 'btn3' => 'Financeiro']], 'class' => 'node-buttons', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '2', 'output' => 'output_1']]]], 'outputs' => [
                                    'output_1' => ['connections' => [['node' => '4', 'output' => 'input_1']]],
                                    'output_2' => ['connections' => [['node' => '5', 'output' => 'input_1']]],
                                    'output_3' => ['connections' => [['node' => '6', 'output' => 'input_1']]]
                                ], 'pos_x' => 700, 'pos_y' => 200],
                                '4' => ['id' => 4, 'name' => 'message', 'data' => ['params' => ['text' => 'Perfeito! Vou te transferir para nossa equipe comercial. Um momento...']], 'class' => 'node-message', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '3', 'output' => 'output_1']]]], 'outputs' => ['output_1' => ['connections' => []]], 'pos_x' => 1100, 'pos_y' => 50],
                                '5' => ['id' => 5, 'name' => 'message', 'data' => ['params' => ['text' => 'Vou te conectar com um técnico agora mesmo. Por favor, descreva o problema brevemente.']], 'class' => 'node-message', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '3', 'output' => 'output_2']]]], 'outputs' => ['output_1' => ['connections' => []]], 'pos_x' => 1100, 'pos_y' => 200],
                                '6' => ['id' => 6, 'name' => 'message', 'data' => ['params' => ['text' => 'Entendido. Para questões financeiras, informe o CPF/CNPJ ou número do pedido.']], 'class' => 'node-message', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '3', 'output' => 'output_3']]]], 'outputs' => ['output_1' => ['connections' => []]], 'pos_x' => 1100, 'pos_y' => 350],
                            ]
                        ]
                    ]
                ]
            ],
            [
                'name' => '💰 Qualificação de Vendas (BANT)',
                'description' => 'Fluxo para qualificar leads coletando orçamento e necessidade.',
                'category' => 'Vendas',
                'is_public' => true,
                'flow_data' => [
                    'drawflow' => [
                        'Home' => [
                            'data' => [
                                '1' => ['id' => 1, 'name' => 'start', 'data' => ['params' => []], 'class' => 'node-start', 'html' => '', 'inputs' => [], 'outputs' => ['output_1' => ['connections' => [['node' => '2', 'output' => 'input_1']]]], 'pos_x' => 50, 'pos_y' => 250],
                                '2' => ['id' => 2, 'name' => 'message', 'data' => ['params' => ['text' => 'Olá! Ficamos felizes com seu interesse. Vamos fazer algumas perguntas rápidas para te atender melhor.']], 'class' => 'node-message', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '1', 'output' => 'output_1']]]], 'outputs' => ['output_1' => ['connections' => [['node' => '3', 'output' => 'input_1']]]], 'pos_x' => 350, 'pos_y' => 250],
                                '3' => ['id' => 3, 'name' => 'input', 'data' => ['params' => ['label' => 'Qual o seu nome completo?', 'variable' => 'lead_nome']], 'class' => 'node-input', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '2', 'output' => 'output_1']]]], 'outputs' => ['output_1' => ['connections' => [['node' => '4', 'output' => 'input_1']]]], 'pos_x' => 650, 'pos_y' => 250],
                                '4' => ['id' => 4, 'name' => 'input', 'data' => ['params' => ['label' => 'Qual o seu orçamento aproximado para este projeto?', 'variable' => 'lead_budget']], 'class' => 'node-input', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '3', 'output' => 'output_1']]]], 'outputs' => ['output_1' => ['connections' => [['node' => '5', 'output' => 'input_1']]]], 'pos_x' => 950, 'pos_y' => 250],
                                '5' => ['id' => 5, 'name' => 'message', 'data' => ['params' => ['text' => 'Obrigado @{{lead_nome}}! Recebemos suas informações. Um consultor entrará em contato em breve.']], 'class' => 'node-message', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '4', 'output' => 'output_1']]]], 'outputs' => ['output_1' => ['connections' => []]], 'pos_x' => 1250, 'pos_y' => 250],
                            ]
                        ]
                    ]
                ]
            ],
            [
                'name' => '📋 Pesquisa de Satisfação',
                'description' => 'Colete feedback dos seus clientes após um atendimento.',
                'category' => 'Pós-Venda',
                'is_public' => true,
                'flow_data' => [
                    'drawflow' => [
                        'Home' => [
                            'data' => [
                                '1' => ['id' => 1, 'name' => 'start', 'data' => ['params' => []], 'class' => 'node-start', 'html' => '', 'inputs' => [], 'outputs' => ['output_1' => ['connections' => [['node' => '2', 'output' => 'input_1']]]], 'pos_x' => 50, 'pos_y' => 200],
                                '2' => ['id' => 2, 'name' => 'message', 'data' => ['params' => ['text' => 'Olá! O que achou do nosso atendimento hoje?']], 'class' => 'node-message', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '1', 'output' => 'output_1']]]], 'outputs' => ['output_1' => ['connections' => [['node' => '3', 'output' => 'input_1']]]], 'pos_x' => 350, 'pos_y' => 200],
                                '3' => ['id' => 3, 'name' => 'buttons', 'data' => ['params' => ['text' => 'Avalie sua experiência:', 'btn1' => '⭐ Excelente', 'btn2' => '😐 Regular', 'btn3' => '😞 Ruim']], 'class' => 'node-buttons', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '2', 'output' => 'output_1']]]], 'outputs' => [
                                    'output_1' => ['connections' => [['node' => '4', 'output' => 'input_1']]],
                                    'output_2' => ['connections' => [['node' => '4', 'output' => 'input_1']]],
                                    'output_3' => ['connections' => [['node' => '4', 'output' => 'input_1']]]
                                ], 'pos_x' => 700, 'pos_y' => 200],
                                '4' => ['id' => 4, 'name' => 'message', 'data' => ['params' => ['text' => 'Muito obrigado pelo feedback! Isso nos ajuda a melhorar constantemente.']], 'class' => 'node-message', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '3', 'output' => 'output_1'], ['node' => '3', 'output' => 'output_2'], ['node' => '3', 'output' => 'output_3']]]], 'outputs' => ['output_1' => ['connections' => []]], 'pos_x' => 1050, 'pos_y' => 200],
                            ]
                        ]
                    ]
                ]
            ]
        ];

        foreach ($templates as $template) {
            ChatbotFlowTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }
}
