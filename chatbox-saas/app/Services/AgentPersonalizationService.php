<?php

namespace App\Services;

use App\Models\Chatbot;
use App\Models\ChatbotFlow;
use App\Models\Company;
use App\Models\KnowledgeBase;
use Illuminate\Support\Facades\Log;

class AgentPersonalizationService
{
    public function generateForSegment(Company $company, Chatbot $chatbot, string $segment, string $objective = 'suporte', array $channels = ['site']): void
    {
        // Define personality based on segment
        $personality = $this->getPersonalityForSegment($segment);

        $companyContext = $this->buildCompanyContext($company);

        $baseInstruction = "Você é o assistente virtual da empresa {$company->name}, do segmento de {$segment}. 
Seu objetivo principal é focar em {$objective} através dos canais: ".implode(', ', $channels).". 
Seu tom deve ser {$personality}.
Abaixo estão os dados da empresa para você usar como contexto ao responder:
{$companyContext}";

        $chatbot->update([
            'ai_instruction' => $baseInstruction,
            'initial_message' => "Olá! Sou o assistente virtual da {$company->name}. Como posso ajudar você hoje?",
        ]);

        // 1. Generate knowledge base and flows (Templates Estáticos Primeiro)
        $this->generateKnowledgeAndFlows($company, $chatbot, $segment);

        // 2. Personalização com IA (opcional/complementar)
        try {
            $aiGenerator = app(AIGeneratorService::class);
            // We just ask AI to generate an engaging initial message based on the rich context
            // and maybe some additional dynamic FAQs. For now, we mainly update the initial message.
            $aiData = $aiGenerator->generateInitialSetup($company, $segment, $objective, $channels);

            if ($aiData) {
                if (isset($aiData['initial_message'])) {
                    $chatbot->update(['initial_message' => $aiData['initial_message']]);
                }
                // We only insert AI data if it brought something new that wasn't in static template
                // But for now, insertGeneratedData will append new flows/knowledge
                $this->insertGeneratedData($company, $aiData);
            }
        } catch (\Exception $e) {
            Log::warning('AI setup generation failed, using static templates only.', ['error' => $e->getMessage()]);
        }
    }

    private function buildCompanyContext(Company $company): string
    {
        $context = '';
        if ($company->website) {
            $context .= "Site: {$company->website}\n";
        }
        if ($company->phone) {
            $context .= "Telefone Fixo: {$company->phone}\n";
        }
        if ($company->whatsapp) {
            $context .= "WhatsApp: {$company->whatsapp}\n";
        }
        if ($company->email) {
            $context .= "E-mail: {$company->email}\n";
        }
        if ($company->address) {
            $context .= "Endereço: {$company->address}\n";
        }

        if ($company->business_hours) {
            $context .= 'Horário de Funcionamento: ';
            if (is_array($company->business_hours)) {
                foreach ($company->business_hours as $day => $hours) {
                    $context .= "{$day}: {$hours}, ";
                }
                $context = rtrim($context, ', ')."\n";
            } else {
                $context .= "{$company->business_hours}\n";
            }
        }

        if ($company->social_networks) {
            $context .= 'Redes Sociais: ';
            if (is_array($company->social_networks)) {
                foreach ($company->social_networks as $platform => $link) {
                    $context .= "{$platform}: {$link}, ";
                }
                $context = rtrim($context, ', ')."\n";
            }
        }

        return $context;
    }

    private function insertGeneratedData(Company $company, array $data): void
    {
        if (isset($data['knowledge']) && is_array($data['knowledge'])) {
            foreach ($data['knowledge'] as $item) {
                KnowledgeBase::create([
                    'company_id' => $company->id,
                    'title' => $item['title'],
                    'content' => $item['content'],
                    'source_type' => 'manual',
                    'is_active' => true,
                ]);
            }
        }

        if (isset($data['flows']) && is_array($data['flows'])) {
            $sortOrder = 0;
            foreach ($data['flows'] as $flow) {
                ChatbotFlow::create([
                    'company_id' => $company->id,
                    'trigger' => $flow['trigger'] ?? 'Opção '.($sortOrder + 1),
                    'question' => $flow['question'] ?? '',
                    'answer' => $flow['answer'] ?? '',
                    'active' => true,
                    'sort_order' => $sortOrder++,
                ]);
            }
        }
    }

    private function getPersonalityForSegment(string $segment): string
    {
        return match ($segment) {
            'Escritório de Advocacia', 'Advogado Autônomo' => 'profissional, formal e ética',
            'Clínica', 'Consultório', 'Clínica Médica', 'Odontologia' => 'acolhedora, empática e prestativa',
            'Restaurante', 'Delivery', 'Lanchonete' => 'amigável, rápida e apetitosa',
            'Academia', 'Personal Trainer', 'Studio Funcional' => 'motivadora, enérgica e direta',
            'Imobiliária', 'Corretor de Imóveis' => 'consultiva, paciente e vendedora',
            'Tecnologia', 'Software House' => 'técnica, objetiva e inovadora',
            'E-commerce', 'Loja Virtual' => 'comercial, ágil e focada em conversão',
            'Salão de Beleza', 'Barbearia', 'Clínica de Estética (Beleza)' => 'atenciosa, descontraída e focada no bem-estar',
            'Pet Shop', 'Veterinária' => 'carinhosa, alegre e apaixonada por animais',
            'Auto Escola' => 'didática, paciente e encorajadora',
            default => 'educada, prestativa e objetiva',
        };
    }

    private function generateKnowledgeAndFlows(Company $company, Chatbot $chatbot, string $segment): void
    {
        $knowledgeItems = [];
        $flows = [];

        // Base general knowledge
        $knowledgeItems[] = [
            'title' => 'Informações Gerais',
            'content' => "A empresa {$company->name} atua no segmento de {$segment}. ".($company->website ? "O site oficial é {$company->website}." : 'Para mais detalhes, consulte nossos atendentes.'),
        ];

        switch ($segment) {
            case 'Academia':
            case 'Personal Trainer':
            case 'Studio de Pilates':
            case 'Studio Funcional':
            case 'Cross Training':
                $knowledgeItems[] = ['title' => 'Planos e Valores', 'content' => 'Temos diversos planos que se adequam ao seu objetivo. Oferecemos planos mensais, trimestrais e anuais.'];
                $knowledgeItems[] = ['title' => 'Horários de Funcionamento', 'content' => 'A academia está aberta de segunda a sexta das 06h às 22h, e aos finais de semana das 08h às 14h.'];
                $knowledgeItems[] = ['title' => 'Avaliação Física', 'content' => 'A avaliação física pode ser agendada na recepção. É recomendada para a elaboração de um treino personalizado.'];
                $knowledgeItems[] = ['title' => 'Matrícula', 'content' => 'A matrícula pode ser feita online ou diretamente na recepção, necessitando de um documento com foto.'];

                $flows[] = ['trigger' => 'Ver planos', 'question' => 'Quais são os planos disponíveis?', 'answer' => 'Nossos planos incluem as opções Mensal, Trimestral e Anual. Qual opção você gostaria de conhecer melhor?'];
                $flows[] = ['trigger' => 'Agendar avaliação', 'question' => 'Como agendar avaliação?', 'answer' => 'Para agendar sua avaliação física, informe seu nome completo e o melhor horário para você.'];
                break;

            case 'Clínica':
            case 'Consultório':
            case 'Odontologia':
            case 'Psicologia':
            case 'Clínica Médica':
            case 'Clínica Popular':
            case 'Clínica Multidisciplinar':
                $knowledgeItems[] = ['title' => 'Especialidades', 'content' => 'Atendemos nas seguintes especialidades: Clínico Geral, Cardiologia, Dermatologia, Pediatria, Ortopedia, Ginecologia, entre outras.'];
                $knowledgeItems[] = ['title' => 'Convênios', 'content' => 'Aceitamos os seguintes convênios: Unimed, Bradesco, Amil, SulAmérica, além de consultas particulares.'];
                $knowledgeItems[] = ['title' => 'Agendamento de Consulta', 'content' => 'Para agendar uma consulta, basta informar a especialidade desejada e verificaremos os horários disponíveis.'];
                $knowledgeItems[] = ['title' => 'Remarcar e Cancelar', 'content' => 'Você pode remarcar ou cancelar sua consulta entrando em contato com nossa equipe com pelo menos 24h de antecedência.'];

                $flows[] = ['trigger' => 'Agendar Consulta', 'question' => 'Gostaria de agendar uma consulta?', 'answer' => 'Com certeza! Para qual especialidade você deseja o agendamento?'];
                $flows[] = ['trigger' => 'Remarcar Consulta', 'question' => 'Preciso remarcar minha consulta', 'answer' => 'Vamos remarcar. Por favor, me informe o seu nome completo e a data atual da consulta.'];
                $flows[] = ['trigger' => 'Cancelar Consulta', 'question' => 'Quero cancelar uma consulta', 'answer' => 'Tudo bem. Poderia me informar seu nome completo para localizarmos o agendamento?'];
                $flows[] = ['trigger' => 'Convênios', 'question' => 'Quais convênios vocês aceitam?', 'answer' => 'Aceitamos Unimed, Bradesco, Amil, SulAmérica e Particular. Qual é o seu plano?'];
                $flows[] = ['trigger' => 'Horários', 'question' => 'Qual o horário de funcionamento?', 'answer' => 'Nosso horário de funcionamento é das 08:00 às 18:00 de segunda a sexta-feira.'];
                $flows[] = ['trigger' => 'Falar com Atendente', 'question' => 'Quero falar com um humano', 'answer' => 'Vou transferir você para nossa recepção. Aguarde um momento.'];
                break;

            case 'Imobiliária':
            case 'Corretor de Imóveis':
                $knowledgeItems[] = ['title' => 'Imóveis para Venda', 'content' => 'Temos um amplo catálogo de imóveis residenciais e comerciais à venda. Acesse nosso site para conhecer.'];
                $knowledgeItems[] = ['title' => 'Locação', 'content' => 'Trabalhamos com aluguel sem fiador. O processo é rápido, digital e sem burocracia.'];
                $knowledgeItems[] = ['title' => 'Agendar Visita', 'content' => 'Para agendar uma visita, basta informar o código do imóvel e sugerir um horário. Nosso corretor acompanhará você.'];
                $knowledgeItems[] = ['title' => 'Avaliação de Imóvel', 'content' => 'Nossos corretores realizam avaliação do seu imóvel para garantir o melhor valor de mercado na venda ou aluguel.'];

                $flows[] = ['trigger' => 'Quero comprar', 'question' => 'Procurando um imóvel para comprar?', 'answer' => 'Excelente! Você procura apartamento, casa, lote ou imóvel comercial?'];
                $flows[] = ['trigger' => 'Quero alugar', 'question' => 'Procurando um imóvel para alugar?', 'answer' => 'Temos ótimas opções. Em qual bairro você gostaria de morar?'];
                $flows[] = ['trigger' => 'Agendar Visita', 'question' => 'Quero visitar um imóvel', 'answer' => 'Perfeito! Qual é o código ou endereço do imóvel que você tem interesse?'];
                break;

            case 'Escritório de Advocacia':
            case 'Advogado Autônomo':
                $knowledgeItems[] = ['title' => 'Áreas de Atuação', 'content' => 'Atuamos em diversas áreas do Direito, prestando consultoria e representação jurídica especializada.'];
                $knowledgeItems[] = ['title' => 'Agendamento de Consulta', 'content' => 'O primeiro atendimento serve para entender o caso e pode ser presencial ou por videoconferência.'];
                $knowledgeItems[] = ['title' => 'Honorários', 'content' => 'Nossos honorários são estabelecidos de forma transparente após a análise preliminar do caso.'];

                $flows[] = ['trigger' => 'Agendar Reunião', 'question' => 'Como posso marcar uma reunião?', 'answer' => 'Para agendar seu atendimento, por favor me informe um breve resumo do seu caso.'];
                $flows[] = ['trigger' => 'Áreas de Atuação', 'question' => 'Quais as áreas de atuação do escritório?', 'answer' => 'Atuamos no consultivo e contencioso. Qual é a natureza do seu problema jurídico?'];
                break;

            case 'Salão de Beleza':
            case 'Barbearia':
            case 'Clínica de Estética (Beleza)':
            case 'Nail Designer':
                $knowledgeItems[] = ['title' => 'Serviços', 'content' => 'Oferecemos cortes, coloração, tratamentos capilares, manicure, pedicure, maquiagem e estética facial/corporal.'];
                $knowledgeItems[] = ['title' => 'Agendamento', 'content' => 'Para agendar, basta nos informar qual serviço deseja e verificaremos a disponibilidade na agenda.'];
                $knowledgeItems[] = ['title' => 'Pacotes', 'content' => 'Temos pacotes especiais para noivas, formaturas e combos semanais com desconto.'];

                $flows[] = ['trigger' => 'Agendar Horário', 'question' => 'Qual serviço você gostaria de agendar?', 'answer' => 'Excelente! Me informe o serviço desejado e a data de preferência.'];
                $flows[] = ['trigger' => 'Valores', 'question' => 'Qual a tabela de preços?', 'answer' => 'Nossos valores variam por serviço e profissional. Qual procedimento você tem interesse?'];
                break;

            case 'Pet Shop':
            case 'Veterinária':
                $knowledgeItems[] = ['title' => 'Banho e Tosa', 'content' => 'Oferecemos banho, tosa higiênica, tosa na tesoura ou máquina, hidratação e corte de unhas.'];
                $knowledgeItems[] = ['title' => 'Clínica Veterinária', 'content' => 'Temos consultas clínicas, vacinação, exames laboratoriais e cirurgias.'];
                $knowledgeItems[] = ['title' => 'Táxi Dog', 'content' => 'Buscamos e levamos o seu pet com total segurança. Consulte a taxa para o seu bairro.'];

                $flows[] = ['trigger' => 'Marcar Banho', 'question' => 'Gostaria de agendar banho e tosa?', 'answer' => 'Que legal! Qual é a raça e o porte do seu pet? Precisa de transporte (Táxi Dog)?'];
                $flows[] = ['trigger' => 'Consulta Vet', 'question' => 'Precisa de atendimento veterinário?', 'answer' => 'Nossos veterinários estão prontos para ajudar. Qual é o sintoma ou motivo da consulta?'];
                break;

            case 'Auto Escola':
                $knowledgeItems[] = ['title' => 'Primeira Habilitação', 'content' => 'O processo inclui exames médico/psicotécnico, curso teórico (CFC) e aulas práticas.'];
                $knowledgeItems[] = ['title' => 'Adição de Categoria', 'content' => 'Para adicionar a categoria A ou B, você precisará apenas realizar novas aulas práticas e exame prático.'];
                $knowledgeItems[] = ['title' => 'Renovação', 'content' => 'Auxiliamos em todo o processo burocrático de renovação da CNH.'];

                $flows[] = ['trigger' => 'Tirar Carteira', 'question' => 'Quer tirar a 1ª Habilitação?', 'answer' => 'Ótima decisão! Você deseja tirar carteira de carro (B), moto (A) ou ambos (AB)?'];
                $flows[] = ['trigger' => 'Valores', 'question' => 'Quais os valores e formas de pagamento?', 'answer' => 'Temos condições facilitadas e parcelamento sem juros. Para qual categoria deseja saber o valor?'];
                break;

            case 'Restaurante':
            case 'Delivery':
            case 'Lanchonete':
            case 'Pizzaria':
            case 'Hamburgueria':
                $knowledgeItems[] = ['title' => 'Cardápio', 'content' => 'Nosso cardápio completo pode ser acessado pelo link no nosso perfil ou site.'];
                $knowledgeItems[] = ['title' => 'Pedidos para Delivery', 'content' => 'Realizamos entregas na região. O tempo estimado é informado no momento da confirmação do pedido.'];
                $knowledgeItems[] = ['title' => 'Reservas', 'content' => 'Aceitamos reservas para pequenos e grandes grupos. Por favor, avise com antecedência.'];
                $knowledgeItems[] = ['title' => 'Horário de Funcionamento', 'content' => 'Estamos abertos de terça a domingo, no almoço e no jantar.'];

                $flows[] = ['trigger' => 'Fazer Pedido', 'question' => 'Quero fazer um pedido (Delivery)', 'answer' => 'Com fome? Que bom! Acesse nosso cardápio digital ou me diga o que deseja pedir.'];
                $flows[] = ['trigger' => 'Fazer Reserva', 'question' => 'Gostaria de reservar uma mesa', 'answer' => 'Ficaremos felizes em receber você! Para qual dia, horário e quantidade de pessoas seria a reserva?'];
                $flows[] = ['trigger' => 'Ver Cardápio', 'question' => 'Pode enviar o cardápio?', 'answer' => 'Claro! Aqui está o link do nosso cardápio digital: [Link do Cardápio]'];
                break;

            default:
                $knowledgeItems[] = ['title' => 'Produtos e Serviços', 'content' => 'Oferecemos produtos e serviços de alta qualidade no segmento de '.$segment.'.'];
                $knowledgeItems[] = ['title' => 'Atendimento', 'content' => 'Nossa equipe está pronta para tirar suas dúvidas e prestar o melhor suporte.'];

                $flows[] = ['trigger' => 'Falar com Atendente', 'question' => 'Quero falar com um humano', 'answer' => 'Vou transferir você para um de nossos especialistas. Aguarde um momento.'];
                break;
        }

        // Insert Knowledge Items
        foreach ($knowledgeItems as $item) {
            KnowledgeBase::create([
                'company_id' => $company->id,
                'title' => $item['title'],
                'content' => $item['content'],
                'source_type' => 'manual',
                'is_active' => true,
            ]);
        }

        // Insert Chatbot Flows
        $sortOrder = 0;
        foreach ($flows as $flow) {
            ChatbotFlow::create([
                'company_id' => $company->id,
                'trigger' => $flow['trigger'],
                'question' => $flow['question'],
                'answer' => $flow['answer'],
                'active' => true,
                'sort_order' => $sortOrder++,
            ]);
        }
    }
}
