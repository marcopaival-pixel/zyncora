<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HelpCategory;
use App\Models\HelpArticle;
use App\Models\Sector;

class HelpCenterSeeder extends Seeder
{
    public function run(): void
    {
        $catConfig = HelpCategory::firstOrCreate(['slug' => 'configuracoes'], ['name' => 'Configurações', 'description' => 'Ajustes gerais do sistema.']);
        $catChat = HelpCategory::firstOrCreate(['slug' => 'chatbots'], ['name' => 'Chatbots', 'description' => 'Configuração dos assistentes virtuais.']);
        $catCrm = HelpCategory::firstOrCreate(['slug' => 'crm'], ['name' => 'CRM e Negócios', 'description' => 'Gestão de clientes e vendas.']);

        $clinica = Sector::where('name', 'like', '%Clínica%')->first();
        $imobiliaria = Sector::where('name', 'like', '%Imobiliária%')->first();

        HelpArticle::updateOrCreate(
            ['slug' => 'como-criar-chatbot'],
            [
                'help_category_id' => $catChat->id,
                'title' => 'Como criar um Chatbot',
                'description' => 'Aprenda a configurar seu primeiro assistente virtual.',
                'content' => '<h3>Passo a passo</h3><ol><li>Acesse o menu Chatbots.</li><li>Clique em "Novo Chatbot".</li><li>Defina o nome e a instrução da IA.</li><li>Salve e teste no painel lateral.</li></ol><h3>Dicas</h3><p>Seja claro na instrução principal da IA para evitar respostas incorretas.</p>',
                'module' => 'Chatbots',
                'examples_by_segment' => [
                    'default' => 'Exemplo: "Você é um assistente de vendas da empresa X. Ajude os clientes a comprar produtos."',
                    ($clinica ? $clinica->id : 'clinica') => 'Exemplo: "Você é a recepcionista da Clínica X. Agende consultas e tire dúvidas sobre os exames disponíveis."',
                    ($imobiliaria ? $imobiliaria->id : 'imobiliaria') => 'Exemplo: "Você é o corretor da Imobiliária X. Mostre os imóveis disponíveis e agende visitas."',
                ]
            ]
        );

        HelpArticle::updateOrCreate(
            ['slug' => 'como-usar-o-crm'],
            [
                'help_category_id' => $catCrm->id,
                'title' => 'Como usar o CRM e Funil',
                'description' => 'Entenda para que serve o funil de negócios.',
                'content' => '<h3>Para que serve?</h3><p>O CRM ajuda a organizar os contatos em etapas (funil).</p><h3>Passo a passo</h3><ol><li>Acesse o menu CRM.</li><li>Arraste os cards de uma coluna para outra.</li></ol>',
                'module' => 'Pipeline',
                'examples_by_segment' => [
                    'default' => 'Exemplo: Mover cliente de "Novo Contato" para "Em Negociação".',
                    ($clinica ? $clinica->id : 'clinica') => 'Exemplo: Mover paciente de "Agendado" para "Atendido".',
                    ($imobiliaria ? $imobiliaria->id : 'imobiliaria') => 'Exemplo: Mover lead de "Visita Agendada" para "Contrato Assinado".',
                ]
            ]
        );
    }
}
