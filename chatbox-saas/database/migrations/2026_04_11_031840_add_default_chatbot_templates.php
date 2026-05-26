<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $supportFlow = ['drawflow' => ['Home' => ['data' => [
            '1' => ['id' => 1, 'name' => 'start', 'data' => ['params' => []], 'class' => 'node-start', 'html' => '', 'inputs' => [], 'outputs' => ['output_1' => ['connections' => [['node' => '2', 'output' => 'input_1']]]], 'pos_x' => 100, 'pos_y' => 300],
            '2' => ['id' => 2, 'name' => 'message', 'data' => ['params' => ['text' => 'Olá! Este é o suporte técnico da Zynkora. Como posso ajudar?']], 'class' => 'node-message', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '1', 'output' => 'output_1']]]], 'outputs' => ['output_1' => ['connections' => [['node' => '3', 'output' => 'input_1']]]], 'pos_x' => 450, 'pos_y' => 300],
            '3' => ['id' => 3, 'name' => 'buttons', 'data' => ['params' => ['text' => 'Escolha uma opção:', 'btn1' => 'Financeiro', 'btn2' => 'Técnico', 'btn3' => 'Outros']], 'class' => 'node-buttons', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '2', 'output' => 'output_1']]]], 'outputs' => ['output_1' => ['connections' => []], 'output_2' => ['connections' => []], 'output_3' => ['connections' => []]], 'pos_x' => 800, 'pos_y' => 300]
        ]]]];

        $salesFlow = ['drawflow' => ['Home' => ['data' => [
            '1' => ['id' => 1, 'name' => 'start', 'data' => ['params' => []], 'class' => 'node-start', 'html' => '', 'inputs' => [], 'outputs' => ['output_1' => ['connections' => [['node' => '2', 'output' => 'input_1']]]], 'pos_x' => 100, 'pos_y' => 300],
            '2' => ['id' => 2, 'name' => 'message', 'data' => ['params' => ['text' => 'Olá! Queremos te ajudar a vender mais.']], 'class' => 'node-message', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '1', 'output' => 'output_1']]]], 'outputs' => ['output_1' => ['connections' => [['node' => '3', 'output' => 'input_1']]]], 'pos_x' => 450, 'pos_y' => 300],
            '3' => ['id' => 3, 'name' => 'input', 'data' => ['params' => ['label' => 'Qual o seu nome?', 'variable' => 'nome']], 'class' => 'node-input', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '2', 'output' => 'output_1']]]], 'outputs' => ['output_1' => ['connections' => [['node' => '4', 'output' => 'input_1']]]], 'pos_x' => 800, 'pos_y' => 300],
            '4' => ['id' => 4, 'name' => 'message', 'data' => ['params' => ['text' => 'Prazer em te conhecer, @{{nome}}!']] , 'class' => 'node-message', 'html' => '', 'inputs' => ['input_1' => ['connections' => [['node' => '3', 'output' => 'output_1']]]], 'outputs' => ['output_1' => ['connections' => []]], 'pos_x' => 1150, 'pos_y' => 300],
        ]]]];

        \Illuminate\Support\Facades\DB::table('chatbot_flow_templates')->insert([
            [
                'name' => 'Suporte Técnico Básico',
                'description' => 'Um fluxo inicial para triagem de suporte com botões.',
                'flow_data' => json_encode($supportFlow),
                'category' => 'Suporte',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Qualificação de Leads',
                'description' => 'Coleta o nome do cliente e faz uma saudação personalizada.',
                'flow_data' => json_encode($salesFlow),
                'category' => 'Vendas',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('chatbot_flow_templates')
            ->whereIn('name', ['Suporte Técnico Básico', 'Qualificação de Leads'])
            ->delete();
    }
};
