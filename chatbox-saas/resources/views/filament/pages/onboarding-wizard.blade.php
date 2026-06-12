<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 min-h-[70vh]">
        
        <!-- Formulário (Esquerda) -->
        <div class="lg:col-span-7 xl:col-span-8 flex flex-col justify-start">
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Vamos criar seu primeiro agente de IA
                </h1>
                <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">
                    Responder clientes, gerar oportunidades e automatizar atendimentos nunca foi tão simples.
                </p>
                
                <div class="mt-6 flex flex-wrap gap-x-6 gap-y-3">
                    <div class="flex items-center text-sm font-medium text-gray-600 dark:text-gray-300">
                        <x-heroicon-s-check-circle class="w-5 h-5 text-primary-500 mr-2" /> Sem cartão de crédito
                    </div>
                    <div class="flex items-center text-sm font-medium text-gray-600 dark:text-gray-300">
                        <x-heroicon-s-check-circle class="w-5 h-5 text-primary-500 mr-2" /> Agente pronto para uso
                    </div>
                    <div class="flex items-center text-sm font-medium text-gray-600 dark:text-gray-300">
                        <x-heroicon-s-check-circle class="w-5 h-5 text-primary-500 mr-2" /> Configuração em < 5 minutos
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-6 md:p-8">
                <form wire:submit="submit">
                    {{ $this->form }}
                </form>
            </div>
        </div>

        <!-- Preview (Direita) -->
        <div class="lg:col-span-5 xl:col-span-4 relative">
            <div class="sticky top-8 rounded-2xl bg-gradient-to-b from-gray-50 to-white dark:from-gray-800/50 dark:to-gray-900 border border-gray-200 dark:border-gray-800 shadow-lg overflow-hidden flex flex-col h-full min-h-[500px]">
                
                <!-- Header Preview -->
                <div class="p-6 border-b border-gray-100 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                            <x-heroicon-o-sparkles class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Prévia do seu agente</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Configuração em tempo real</p>
                        </div>
                    </div>
                </div>

                <!-- Corpo Preview -->
                <div class="p-6 flex-1 flex flex-col" x-data="{ segment: $wire.$entangle('data.segment_primary').live }">
                    
                    <template x-if="!segment">
                        <div class="flex-1 flex flex-col items-center justify-center text-center opacity-60">
                            <x-heroicon-o-cpu-chip class="w-16 h-16 text-gray-400 mb-4 animate-pulse" />
                            <p class="text-sm text-gray-500 dark:text-gray-400">Aguardando seleção do segmento para preparar as habilidades do seu agente...</p>
                        </div>
                    </template>

                    <template x-if="segment">
                        <div class="animate-fade-in">
                            <div class="mb-6 flex items-center justify-between">
                                <span class="px-3 py-1 text-xs font-medium bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-full" x-text="'Segmento: ' + segment"></span>
                                <span class="flex items-center text-xs text-green-600 dark:text-green-400 font-medium"><x-heroicon-s-check-circle class="w-4 h-4 mr-1"/> Otimizado</span>
                            </div>

                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 uppercase tracking-wider">Habilidades Geradas:</h4>
                            
                            <ul class="space-y-4">
                                <template x-for="(skill, index) in getSkillsForSegment(segment)" :key="index">
                                    <li class="flex items-start gap-3 animate-slide-up" :style="'animation-delay: ' + (index * 150) + 'ms'">
                                        <div class="mt-0.5 rounded-full p-1 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200" x-text="skill.title"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="skill.desc"></p>
                                        </div>
                                    </li>
                                </template>
                            </ul>

                            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Base de Conhecimento:</span>
                                    <span class="font-medium text-primary-600 dark:text-primary-400">Pronta para uso</span>
                                </div>
                                <div class="flex items-center justify-between text-sm mt-2">
                                    <span class="text-gray-500 dark:text-gray-400">Personalidade:</span>
                                    <span class="font-medium text-primary-600 dark:text-primary-400">Ajustada</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts and Styles for specific Premium animations -->
    <style>
        .animate-fade-in {
            animation: fadeIn 0.4s ease-out forwards;
        }
        .animate-slide-up {
            opacity: 0;
            transform: translateY(10px);
            animation: slideUp 0.4s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            window.getSkillsForSegment = function(segment) {
                const defaultSkills = [
                    { title: 'Tirar dúvidas gerais', desc: 'Responde perguntas sobre localização, horários e serviços.' },
                    { title: 'Captar leads 24/7', desc: 'Coleta nome e telefone de clientes interessados.' },
                    { title: 'Redirecionar para humanos', desc: 'Transfere o atendimento em casos complexos.' }
                ];
                
                const segmentSkills = {
                    'Clínica': [
                        { title: 'Informar especialidades', desc: 'Lista médicos e serviços oferecidos.' },
                        { title: 'Pré-agendamento', desc: 'Coleta intenção de data e horário para consultas.' },
                        { title: 'Dúvidas sobre convênios', desc: 'Informa os planos de saúde aceitos.' }
                    ],
                    'Academia': [
                        { title: 'Informar planos e preços', desc: 'Apresenta pacotes mensais, trimestrais e anuais.' },
                        { title: 'Agendar aulas experimentais', desc: 'Capta interessados para o primeiro treino grátis.' },
                        { title: 'Grade de horários', desc: 'Informa horários de modalidades (Spinning, Fit Dance).' }
                    ],
                    'Escritório de Advocacia': [
                        { title: 'Triagem jurídica', desc: 'Entende a área do problema (Trabalhista, Civil, etc).' },
                        { title: 'Agendamento de consulta', desc: 'Marca horário com o advogado especialista.' },
                        { title: 'Informar sobre o escritório', desc: 'Passa credibilidade e áreas de atuação.' }
                    ],
                    'E-commerce': [
                        { title: 'Status do pedido', desc: 'Informa onde está a mercadoria do cliente.' },
                        { title: 'Política de trocas', desc: 'Tira dúvidas sobre devolução e prazos.' },
                        { title: 'Ajuda na compra', desc: 'Recomenda produtos e tira dúvidas técnicas.' }
                    ],
                    'Imobiliária': [
                        { title: 'Busca de imóveis', desc: 'Pergunta tipo, bairro e valor desejado.' },
                        { title: 'Agendar visita', desc: 'Marca visitações com corretores disponíveis.' },
                        { title: 'Dúvidas sobre locação', desc: 'Explica sobre fiador, caução e garantias.' }
                    ],
                    'Salão de Beleza': [
                        { title: 'Tabela de serviços', desc: 'Informa valores de corte, coloração, manicure.' },
                        { title: 'Agendamento', desc: 'Reserva horário com os profissionais.' },
                        { title: 'Dicas rápidas', desc: 'Tira dúvidas sobre cuidados pós-procedimento.' }
                    ],
                    'Restaurante': [
                        { title: 'Mostrar cardápio', desc: 'Apresenta as opções de pratos e valores.' },
                        { title: 'Reservar mesa', desc: 'Garante o lugar para datas especiais.' },
                        { title: 'Receber pedidos', desc: 'Anota o pedido para delivery ou retirada.' }
                    ]
                };

                return segmentSkills[segment] || defaultSkills;
            };
        });
    </script>
</x-filament-panels::page>
