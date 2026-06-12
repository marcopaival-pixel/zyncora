<x-filament-panels::page>
    <div class="flex flex-col gap-4 h-[calc(100vh-160px)] -mt-4" x-data="{ 
        isSaving: false, 
        lastSaved: null,
        autoSave() {
            this.isSaving = true;
            const data = editor.export();
            $wire.saveFlow(data).then(() => {
                this.isSaving = false;
                this.lastSaved = new Date().toLocaleTimeString();
            });
        }
    }">
        <!-- Barra de contexto (ações extra: modelos e simulador) -->
        <div class="flex flex-col gap-3 rounded-2xl border border-gray-200/80 bg-white/70 p-4 shadow-sm backdrop-blur-md dark:border-gray-800 dark:bg-gray-950/60 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 flex-col gap-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-primary-700 dark:text-primary-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Rascunho
                    </span>
                    @if($this->record)
                        <span class="truncate text-sm font-semibold text-gray-900 dark:text-white" title="{{ $this->record->name }}">{{ $this->record->name }}</span>
                    @endif
                </div>
                <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400" x-text="isSaving ? 'A guardar alterações…' : (lastSaved ? 'Último guardado às ' + lastSaved : 'Alterações são guardadas ao premir Salvar no topo, ou por debounce ao editar.')"></span>
            </div>

            <div class="flex flex-shrink-0 flex-wrap items-center gap-2">
                <button type="button" onclick="openTemplatesModal()" class="inline-flex items-center gap-2 rounded-xl border border-amber-500/25 bg-amber-500/10 px-3 py-2 text-xs font-semibold text-amber-800 shadow-sm transition hover:bg-amber-500 hover:text-white dark:text-amber-200">
                    <x-heroicon-o-square-3-stack-3d class="h-4 w-4"/>
                    Modelos
                </button>
                <button type="button" onclick="simulateFlow()" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-md transition hover:bg-indigo-700 active:scale-[0.98]">
                    <x-heroicon-o-play class="h-4 w-4"/>
                    Simular fluxo
                </button>
            </div>
        </div>

        <div class="flex flex-1 gap-4 overflow-hidden relative">
            
            <!-- Sidebar: Elementos do Fluxo (Left) -->
            <div class="w-72 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-xl flex flex-col gap-6 overflow-y-auto z-10">
                
                <div class="space-y-6">
                    <div>
                        <h3 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-4">Essenciais</h3>
                        <div class="grid grid-cols-1 gap-2">
                            @php
                                $nodes = [
                                    ['type' => 'trigger', 'label' => 'Gatilho / Frase', 'icon' => 'chat-bubble-oval-left-ellipsis', 'desc' => 'Inicia fluxo por palavra', 'border' => 'hover:border-pink-500', 'iconWrap' => 'bg-pink-500 shadow-lg shadow-pink-500/20'],
                                    ['type' => 'message', 'label' => 'Mensagem', 'icon' => 'chat-bubble-left-right', 'desc' => 'Enviar texto simples', 'border' => 'hover:border-blue-500', 'iconWrap' => 'bg-blue-500 shadow-lg shadow-blue-500/20'],
                                    ['type' => 'input', 'label' => 'Pergunta', 'icon' => 'pencil-square', 'desc' => 'Esperar resposta', 'border' => 'hover:border-purple-500', 'iconWrap' => 'bg-purple-500 shadow-lg shadow-purple-500/20'],
                                    ['type' => 'condition', 'label' => 'Condição', 'icon' => 'arrows-right-left', 'desc' => 'Desvios lógicos', 'border' => 'hover:border-amber-500', 'iconWrap' => 'bg-amber-500 shadow-lg shadow-amber-500/20'],
                                ];
                            @endphp
                            @foreach($nodes as $node)
                                <div draggable="true" ondragstart="drag(event)" data-node="{{ $node['type'] }}"
                                    class="group flex cursor-grab items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900 {{ $node['border'] }}">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg text-white {{ $node['iconWrap'] }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $node['icon']" class="h-5 w-5"/>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $node['label'] }}</span>
                                        <span class="text-[9px] text-gray-400">{{ $node['desc'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-4">WhatsApp</h3>
                        <div class="grid grid-cols-1 gap-2">
                            @php
                                $nodes = [
                                    ['type' => 'buttons', 'label' => 'Botões', 'icon' => 'square-2-stack', 'desc' => 'Menu interativo', 'border' => 'hover:border-emerald-500', 'iconWrap' => 'bg-emerald-500 shadow-lg shadow-emerald-500/20'],
                                    ['type' => 'list', 'label' => 'Lista', 'icon' => 'list-bullet', 'desc' => 'Menu de opções', 'border' => 'hover:border-teal-500', 'iconWrap' => 'bg-teal-600 shadow-lg shadow-teal-600/20'],
                                ];
                            @endphp
                            @foreach($nodes as $node)
                                <div draggable="true" ondragstart="drag(event)" data-node="{{ $node['type'] }}"
                                    class="group flex cursor-grab items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900 {{ $node['border'] }}">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg text-white {{ $node['iconWrap'] }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $node['icon']" class="h-5 w-5"/>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $node['label'] }}</span>
                                        <span class="text-[9px] text-gray-400">{{ $node['desc'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-4">Ações e integração</h3>
                        <div class="grid grid-cols-1 gap-2">
                             @php
                                $nodes = [
                                    ['type' => 'action', 'label' => 'Executar ação', 'icon' => 'bolt', 'desc' => 'API, humano, setores', 'border' => 'hover:border-rose-500', 'iconWrap' => 'bg-rose-500 shadow-lg shadow-rose-500/20'],
                                    ['type' => 'wait', 'label' => 'Aguardar', 'icon' => 'clock', 'desc' => 'Intervalo entre mensagens', 'border' => 'hover:border-slate-500', 'iconWrap' => 'bg-slate-500 shadow-lg shadow-slate-500/20'],
                                    ['type' => 'end', 'label' => 'Finalizar', 'icon' => 'no-symbol', 'desc' => 'Encerrar fluxo', 'border' => 'hover:border-gray-500', 'iconWrap' => 'bg-gray-600 shadow-lg shadow-gray-600/20'],
                                ];
                            @endphp
                            @foreach($nodes as $node)
                                <div draggable="true" ondragstart="drag(event)" data-node="{{ $node['type'] }}"
                                    class="group flex cursor-grab items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900 {{ $node['border'] }}">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg text-white {{ $node['iconWrap'] }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $node['icon']" class="h-5 w-5"/>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $node['label'] }}</span>
                                        <span class="text-[9px] text-gray-400">{{ $node['desc'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-800">
                    <div class="p-3 bg-indigo-50 dark:bg-indigo-900/10 rounded-xl border border-indigo-100 dark:border-indigo-800/50">
                        <p class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest mb-1">Dica de Variável</p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Em mensagens, use <code class="rounded bg-white px-1 dark:bg-gray-800">@{{nome}}</code> e outras variáveis definidas nos blocos anteriores.</p>
                    </div>
                </div>
            </div>

            <!-- Canvas: Editor Visual -->
            <div class="flex-1 bg-white/50 dark:bg-gray-950/50 border border-gray-200 dark:border-gray-800 rounded-3xl shadow-inner relative overflow-hidden group/canvas" id="drawflow-container" ondrop="drop(event)" ondragover="allowDrop(event)">
                <div id="drawflow" class="w-full h-full"></div>
                
                <!-- Zoom Controls -->
                <div class="absolute bottom-6 left-6 flex gap-1 rounded-2xl border border-gray-200 bg-white/90 p-1.5 shadow-2xl backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/90">
                    <button type="button" onclick="editor.zoom_out()" class="rounded-xl p-2 transition-all hover:bg-gray-100 dark:hover:bg-gray-800" title="Afastar">
                        <x-heroicon-o-minus class="h-4 w-4 text-gray-500"/>
                    </button>
                    <button type="button" onclick="editor.zoom_reset()" class="rounded-xl px-3 text-[10px] font-bold text-gray-500 transition-all hover:bg-gray-100 dark:hover:bg-gray-800">100%</button>
                    <button type="button" onclick="editor.zoom_in()" class="rounded-xl p-2 transition-all hover:bg-gray-100 dark:hover:bg-gray-800" title="Aproximar">
                        <x-heroicon-o-plus class="h-4 w-4 text-gray-500"/>
                    </button>
                </div>
            </div>

            <!-- Property Sidebar (Right) -->
            <div id="property-sidebar" class="fixed right-0 top-14 z-40 flex h-[calc(100vh-3.5rem)] w-80 max-w-[100vw] translate-x-full flex-col border-l border-gray-200 bg-white/95 shadow-2xl backdrop-blur-2xl transition-transform duration-300 dark:border-gray-800 dark:bg-gray-900/95">
                <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/20">
                    <div class="flex items-center gap-3">
                        <div id="prop-icon" class="w-9 h-9 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/20"></div>
                        <div>
                            <h4 id="prop-title" class="text-sm font-bold uppercase tracking-tight text-gray-900 dark:text-white">Propriedades do bloco</h4>
                            <span class="text-[10px] text-gray-500 font-bold uppercase tracking-widest" id="prop-node-id">#ID</span>
                        </div>
                    </div>
                    <button type="button" onclick="closePropertySidebar()" class="rounded-xl p-2 transition-colors hover:bg-gray-200 dark:hover:bg-gray-700">
                        <x-heroicon-o-x-mark class="h-5 w-5"/>
                    </button>
                </div>
                
                <div id="prop-content" class="flex-1 overflow-y-auto p-6 space-y-6">
                    <!-- Dynamic Content -->
                </div>

                <div class="p-6 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-3">
                    <button type="button" id="delete-node-btn" class="flex w-full items-center justify-center gap-2 rounded-xl border border-transparent py-3 text-xs font-bold uppercase tracking-widest text-red-500 transition-all hover:border-red-100 hover:bg-red-50 dark:hover:bg-red-900/10">
                        <x-heroicon-o-trash class="h-4 w-4"/>
                        Remover bloco
                    </button>
                </div>
            </div>

        </div>

        <!-- Templates Modal -->
        <div id="templates-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[100] hidden items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] w-full max-w-4xl h-[600px] flex flex-col shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden animate-in zoom-in-95">
                <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
                    <div>
                        <h3 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Modelos prontos</h3>
                        <p class="text-sm text-gray-500">Comece a partir de um fluxo já montado e adapte à sua empresa.</p>
                    </div>
                    <button type="button" onclick="closeTemplatesModal()" class="rounded-2xl bg-white p-3 shadow-sm transition-all hover:shadow-md dark:bg-gray-800">
                        <x-heroicon-o-x-mark class="h-6 w-6"/>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-8 grid grid-cols-2 gap-6" id="templates-list">
                    @forelse($this->templates as $template)
                        <div onclick="applyTemplate({{ json_encode($template->flow_data) }})" class="group relative p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl hover:border-indigo-500 transition-all cursor-pointer shadow-sm hover:shadow-xl">
                            <div class="absolute top-4 right-4 px-2 py-0.5 bg-indigo-500/10 text-indigo-500 text-[8px] font-bold uppercase tracking-widest rounded">{{ $template->category }}</div>
                            <h4 class="font-bold text-lg text-gray-900 dark:text-white mb-2">{{ $template->name }}</h4>
                            <p class="text-xs text-gray-500 leading-relaxed">{{ $template->description }}</p>
                            <div class="mt-4 flex items-center gap-2 text-[10px] font-bold text-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                <x-heroicon-o-plus-circle class="w-4 h-4"/>
                                Usar este modelo
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 flex flex-col items-center justify-center p-12 text-center bg-gray-50 dark:bg-gray-800/50 rounded-3xl border border-dashed border-gray-200 dark:border-gray-700">
                            <x-heroicon-o-inbox class="mb-4 h-12 w-12 text-gray-300 dark:text-gray-600"/>
                            <p class="text-sm font-medium text-gray-500">Nenhum modelo disponível no momento.</p>
                            <p class="text-[10px] text-gray-400 mt-1">Crie templates no módulo de Modelos de Fluxo.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Simulator Modal -->
        <div id="simulator-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[100] hidden items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] w-full max-w-lg h-[800px] flex flex-col shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden animate-in zoom-in-95">
                <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/30">
                                <x-heroicon-o-cpu-chip class="w-7 h-7"/>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">Simulador do fluxo</h4>
                        </div>
                    </div>
                    <button type="button" onclick="closeSimulator()" class="rounded-xl p-2 transition-colors hover:bg-gray-200 dark:hover:bg-gray-700">
                        <x-heroicon-o-x-mark class="h-6 w-6"/>
                    </button>
                </div>
                
                <div id="chat-messages" class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50 dark:bg-gray-950 scroll-smooth">
                    <!-- Messages -->
                </div>

                <div class="p-6 border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-950">
                    <div id="sim-options-container" class="mb-4 flex flex-wrap gap-2"></div>
                    <div class="flex gap-3 bg-gray-100 dark:bg-gray-900 p-2 rounded-2xl border dark:border-gray-800">
                        <input type="text" id="chat-input" placeholder="Escreva a sua resposta…" class="flex-1 rounded-xl border-none bg-transparent px-4 py-3 text-sm text-gray-700 focus:ring-0 dark:text-gray-200">
                        <button type="button" onclick="sendSimulatedMessage()" class="rounded-xl bg-indigo-600 p-3 text-white shadow-lg transition-all hover:bg-indigo-700 active:scale-90">
                            <x-heroicon-o-paper-airplane class="h-6 w-6"/>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/jerosoler/Drawflow/dist/drawflow.min.css">
    <script src="https://cdn.jsdelivr.net/gh/jerosoler/Drawflow/dist/drawflow.min.js"></script>
    <style>
        #drawflow {
            background-size: 32px 32px;
            background-image: radial-gradient(circle, rgba(128, 128, 128, 0.15) 0.8px, transparent 0.8px);
            background-color: #f8fafc;
        }
        .dark #drawflow {
            background-color: #020617;
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0.8px, transparent 0.8px);
        }

        .drawflow .drawflow-node {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 24px;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.08);
            width: 260px !important;
            padding: 0;
            overflow: hidden;
            transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
        }
        .dark .drawflow .drawflow-node {
            background: rgba(15, 23, 42, 0.95);
            border-color: rgba(51, 65, 85, 0.6);
            box-shadow: 0 20px 50px -20px rgba(0, 0, 0, 0.4);
        }

        .drawflow .drawflow-node .title {
            padding: 14px 20px;
            font-weight: 900;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .drawflow .drawflow-node .content {
            padding: 20px;
            font-size: 11px;
            color: #475569;
            line-height: 1.6;
            min-height: 60px;
        }
        .dark .drawflow .drawflow-node .content {
            color: #94a3b8;
        }

        .drawflow .drawflow-node.selected {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1), 0 25px 50px -12px rgba(99, 102, 241, 0.2);
        }

        .drawflow .connection .main-path {
            stroke: #cbd5e1;
            stroke-width: 3px;
        }
        .dark .drawflow .connection .main-path {
            stroke: #334155;
        }
        .drawflow .connection.selected .main-path {
            stroke: #6366f1;
            stroke-width: 4px;
        }

        .drawflow .point {
            background: #fff;
            border: 3.5px solid #6366f1;
            width: 14px;
            height: 14px;
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.4);
        }

        .node-message .title { background: #3b82f6; }
        .node-input .title { background: #a855f7; }
        .node-condition .title { background: #f59e0b; }
        .node-buttons .title { background: #10b981; }
        .node-list .title { background: #059669; }
        .node-action .title { background: #f43f5e; }
        .node-wait .title { background: #64748b; }
        .node-start .title { background: #000; }
        .node-end .title { background: #1e293b; }

        .prop-input {
            @apply block w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50 p-4 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder-gray-400 font-medium;
        }
        .prop-label {
            @apply block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-3;
        }
    </style>

    <script>
        var drawflowContainer = document.getElementById("drawflow");
        if (window.editor) { window.editor.clear(); }
        var editor = new Drawflow(drawflowContainer);
        window.editor = editor;
        
        editor.reroute = true;
        editor.start();

        var selectedNodeId = null;
        var flowVariables = {};

        var icons = {
            'chat-bubble-left-right': `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>`,
            'pencil-square': `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>`,
            'arrows-right-left': `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>`,
            'square-2-stack': `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16.5 8.25V6a2.25 2.25 0 00-2.25-2.25H6A2.25 2.25 0 003.75 6v8.25A2.25 2.25 0 006 14.25h2.25m4.5 1.5H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-7.5A2.25 2.25 0 018.25 18v-1.5m4.5-4.5H18a2.25 2.25 0 012.25 2.25V12A2.25 2.25 0 0118 14.25h-7.5A2.25 2.25 0 018.25 12V4.5A2.25 2.25 0 0110.5 2.25h4.5A2.25 2.25 0 0117.25 4.5V12"></path></svg>`,
            'list-bullet': `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path></svg>`,
            'bolt': `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"></path></svg>`,
            'clock': `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
            'no-symbol': `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>`,
            'chat-bubble-oval-left-ellipsis': `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"></path></svg>`
        };

        var nodeConfigs = {
            'start': { title: 'Início do fluxo', icon: icons['bolt'], color: '#000' },
            'message': { title: 'Enviar mensagem', icon: icons['chat-bubble-left-right'], color: '#3b82f6' },
            'input': { title: 'Pedir resposta', icon: icons['pencil-square'], color: '#a855f7' },
            'condition': { title: 'Condição', icon: icons['arrows-right-left'], color: '#f59e0b' },
            'buttons': { title: 'Botões (WhatsApp)', icon: icons['square-2-stack'], color: '#10b981' },
            'list': { title: 'Lista (WhatsApp)', icon: icons['list-bullet'], color: '#059669' },
            'action': { title: 'Executar ação', icon: icons['bolt'], color: '#f43f5e' },
            'wait': { title: 'Espera', icon: icons['clock'], color: '#64748b' },
            'end': { title: 'Fim do fluxo', icon: icons['no-symbol'], color: '#1e293b' },
            'trigger': { title: 'Gatilho', icon: icons['chat-bubble-oval-left-ellipsis'], color: '#ec4899' }
        };

        var initialData = @json($this->record->flow_data);
        var nodeStats = @json($this->nodeStats) || {};

        function renderNodeStats() {
            if (!editor.drawflow.drawflow.Home.data) return;
            Object.keys(editor.drawflow.drawflow.Home.data).forEach(id => {
                const stat = nodeStats[id];
                if (stat) {
                    const nodeEl = document.getElementById('node-' + id);
                    if (nodeEl && !nodeEl.querySelector('.node-stats-badge')) {
                        const badge = document.createElement('div');
                        badge.className = 'node-stats-badge absolute -top-3 -right-3 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 px-2 py-1 text-[9px] font-bold text-gray-600 dark:text-gray-300 flex items-center gap-2 z-50';
                        badge.innerHTML = `<span class="flex items-center gap-1 text-blue-500" title="Acessos/Visualizações"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> ${stat.total_views || 0}</span>
                        <span class="flex items-center gap-1 text-rose-500" title="Transferências (Humanas)"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> ${stat.total_transfers || 0}</span>`;
                        nodeEl.appendChild(badge);
                    }
                }
            });
            document.querySelectorAll('.drawflow .drawflow-node').forEach(el => {
                el.style.overflow = 'visible';
            });
        }

        if (initialData && initialData.drawflow) {
            editor.import(initialData);
            setTimeout(renderNodeStats, 100);
        } else {
            setTimeout(() => { addNodeToEditor('start', 300, 200); renderNodeStats(); }, 100);
        }

        var saveTimeout;
        var debouncedSave = () => {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                document.querySelector('[x-data]').__x.$data.autoSave();
                
                // Live preview do simulador
                const simOverlay = document.getElementById('simulator-overlay');
                if (simOverlay && !simOverlay.classList.contains('hidden')) {
                    simulateFlow();
                }
            }, 3000);
        };

        editor.on('nodeCreated', debouncedSave);
        editor.on('nodeRemoved', debouncedSave);
        editor.on('connectionCreated', debouncedSave);
        editor.on('connectionRemoved', debouncedSave);
        editor.on('nodeMoved', debouncedSave);

        editor.on('nodeSelected', function(id) {
            selectedNodeId = id;
            openPropertySidebar(id);
        });

        editor.on('nodeUnselected', closePropertySidebar);

        function allowDrop(ev) { ev.preventDefault(); }
        function drag(ev) { ev.dataTransfer.setData("node", ev.target.closest('[data-node]').getAttribute('data-node')); }
        function drop(ev) {
            ev.preventDefault();
            const data = ev.dataTransfer.getData("node");
            addNodeToEditor(data, ev.clientX, ev.clientY);
        }

        function addNodeToEditor(name, pos_x, pos_y) {
            const rect = drawflowContainer.getBoundingClientRect();
            pos_x = (pos_x - rect.left) * (drawflowContainer.clientWidth / (drawflowContainer.clientWidth * editor.zoom));
            pos_y = (pos_y - rect.top) * (drawflowContainer.clientHeight / (drawflowContainer.clientHeight * editor.zoom));

            const config = nodeConfigs[name];
            const inputs = (name === 'start' || name === 'trigger') ? 0 : 1;
            let outputs = 1;
            if (name === 'end') outputs = 0;
            if (name === 'condition') outputs = 2;
            if (name === 'buttons' || name === 'list') outputs = 3;
            
            const nodeData = { type: name, params: {} };
            const html = `<div class="node-wrapper">
                <div class="title" style="background: ${config.color}">${config.icon} <span>${config.title}</span></div>
                <div class="content">Defina os detalhes no painel à direita.</div>
            </div>`;

            editor.addNode(name, inputs, outputs, pos_x, pos_y, `node-${name}`, nodeData, html);
        }

        function openPropertySidebar(nodeId) {
            const node = editor.getNodeFromId(nodeId);
            const sidebar = document.getElementById('property-sidebar');
            const content = document.getElementById('prop-content');
            
            sidebar.style.transform = 'translateX(0)';
            document.getElementById('prop-node-id').innerText = `#${nodeId} (${node.name})`;
            
            const config = nodeConfigs[node.name];
            const iconEl = document.getElementById('prop-icon');
            iconEl.style.backgroundColor = config.color;
            iconEl.innerHTML = config.icon;

            renderProperties(node);
        }

        function renderProperties(node) {
            const p = node.data.params || {};
            let html = '';

            switch(node.name) {
                case 'trigger':
                    html = `<label class="prop-label">Frases ou Palavras-chave</label>
                            <textarea id="p-keywords" class="prop-input" rows="3" placeholder="ex.: quero agendar, marcar consulta, agendamento (separado por vírgula)">${p.keywords || ''}</textarea>
                            <p class="mt-3 text-[10px] leading-relaxed text-gray-500 dark:text-gray-400">Se o usuário enviar alguma destas frases, a conversa pula direto para cá, ignorando o Início do fluxo.</p>`;
                    break;
                case 'message':
                    html = `<label class="prop-label">Texto da mensagem</label>
                            <textarea id="p-text" class="prop-input" rows="5">${p.text || ''}</textarea>`;
                    break;
                case 'input':
                    html = `<label class="prop-label">Pergunta ao visitante</label>
                            <input type="text" id="p-label" class="prop-input" value="${p.label || ''}">
                            <label class="prop-label mt-4">Variável destino</label>
                            <input type="text" id="p-variable" class="prop-input" value="${p.variable || ''}" placeholder="ex.: nome_cliente">`;
                    break;
                case 'buttons':
                    html = `<label class="prop-label">Texto introdutório</label>
                            <textarea id="p-text" class="prop-input" rows="3">${p.text || ''}</textarea>
                            <div class="mt-4 space-y-3">
                                <label class="prop-label">Texto dos botões</label>
                                <input type="text" id="p-btn1" class="prop-input" placeholder="Opção 1" value="${p.btn1 || ''}">
                                <input type="text" id="p-btn2" class="prop-input" placeholder="Opção 2" value="${p.btn2 || ''}">
                                <input type="text" id="p-btn3" class="prop-input" placeholder="Opção 3" value="${p.btn3 || ''}">
                            </div>`;
                    break;
                case 'list':
                    html = `<label class="prop-label">Texto de cabeçalho</label>
                            <textarea id="p-text" class="prop-input" rows="3">${p.text || ''}</textarea>
                            <label class="prop-label mt-4">Título da secção (lista)</label>
                            <input type="text" id="p-section_title" class="prop-input" value="${p.section_title || ''}" placeholder="Ex.: Escolha uma opção">
                            <div class="mt-4 space-y-3">
                                <label class="prop-label">Linhas (até 3)</label>
                                <input type="text" id="p-item1" class="prop-input" placeholder="Linha 1" value="${p.item1 || ''}">
                                <input type="text" id="p-item2" class="prop-input" placeholder="Linha 2" value="${p.item2 || ''}">
                                <input type="text" id="p-item3" class="prop-input" placeholder="Linha 3" value="${p.item3 || ''}">
                            </div>
                            <p class="mt-3 text-[10px] leading-relaxed text-gray-500 dark:text-gray-400">Cada linha com texto liga a uma saída (1–3) à direita do nó, como nos botões.</p>`;
                    break;
                case 'condition':
                    html = `<label class="prop-label">Variável</label>
                            <input type="text" id="p-var" class="prop-input" value="${p.var || ''}">
                            <label class="prop-label mt-4">Critério</label>
                            <select id="p-op" class="prop-input">
                                <option value="filled" ${p.op==='filled'?'selected':''}>Tem valor</option>
                                <option value="equals" ${p.op==='equals'?'selected':''}>Igual a</option>
                            </select>
                            <input type="text" id="p-val" class="prop-input mt-2" placeholder="Valor a comparar" value="${p.val || ''}">
                            <p class="mt-3 text-[10px] leading-relaxed text-gray-500 dark:text-gray-400">Saída de cima = verdadeiro; saída de baixo = falso (ligue cada ramo no editor).</p>`;
                    break;
                case 'action':
                    html = `<label class="prop-label">Tipo de ação</label>
                            <select id="p-type" class="prop-input">
                                <option value="transfer" ${p.type==='transfer'?'selected':''}>Transferir para humano</option>
                                <option value="webhook" ${p.type==='webhook'?'selected':''}>Webhook (POST)</option>
                                <option value="close" ${p.type==='close'?'selected':''}>Encerrar pedido</option>
                            </select>`;
                    break;
                case 'wait':
                    html = `<label class="prop-label">Pausa (segundos)</label>
                            <input type="number" id="p-seconds" class="prop-input" value="${p.seconds || 2}">`;
                    break;
                case 'start':
                case 'end':
                    html = `<p class="text-xs text-gray-500 dark:text-gray-400">Este tipo de nó não tem parâmetros editáveis. Use as ligações entre nós para definir o percurso.</p>`;
                    break;
                default:
                    html = `<p class="text-xs text-gray-500 dark:text-gray-400">Sem campos específicos para este bloco no editor.</p>`;
                    break;
            }

            document.getElementById('prop-content').innerHTML = html;
            
            const bindParam = (e) => {
                const id = e.target.id || '';
                if (! id.startsWith('p-')) return;
                const key = id.replace(/^p-/, '');
                node.data.params[key] = e.target.value;
                updatePreview(node);
                debouncedSave();
            };
            document.querySelectorAll('.prop-input').forEach(el => {
                el.oninput = bindParam;
                el.onchange = bindParam;
            });
        }

        function updatePreview(node) {
            const el = document.querySelector(`#node-${node.id} .content`);
            if (!el) return;
            const p = node.data.params;
            if (node.name === 'message') el.innerText = p.text || '…';
            else if (node.name === 'trigger') el.innerText = p.keywords || '…';
            else if (node.name === 'input') el.innerText = p.label || '…';
            else if (node.name === 'buttons' || node.name === 'list') el.innerText = p.text || '…';
            else if (node.name === 'condition') el.innerText = (p.var ? 'Se «' + p.var + '»' : 'Condição');
            else if (node.name === 'wait') el.innerText = 'Espera ' + (p.seconds != null ? p.seconds : 2) + ' s';
            else el.innerText = 'Definido';
        }

        function closePropertySidebar() {
            document.getElementById('property-sidebar').style.transform = 'translateX(100%)';
            selectedNodeId = null;
        }

        document.getElementById('delete-node-btn').onclick = () => {
            if (selectedNodeId) {
                editor.removeNodeId('node-' + selectedNodeId);
                closePropertySidebar();
                debouncedSave();
            }
        };

        function openTemplatesModal() { document.getElementById('templates-overlay').classList.replace('hidden', 'flex'); }
        function closeTemplatesModal() { document.getElementById('templates-overlay').classList.replace('flex', 'hidden'); }

        function applyTemplate(data) {
            if (!confirm('Isto substitui o fluxo atual no editor. Alterações não guardadas perdem-se. Continuar?')) return;
            try {
                editor.clear();
                // O data já vem como objeto JS do Blade json_encode
                editor.import(data);
                closeTemplatesModal();
                debouncedSave();
            } catch (e) {
                console.error('Erro ao importar modelo:', e);
                alert('Não foi possível aplicar o modelo. Verifique o formato do fluxo.');
            }
        }

        function simulateFlow() {
            document.getElementById('simulator-overlay').classList.replace('hidden', 'flex');
            const chat = document.getElementById('chat-messages');
            chat.innerHTML = '';
            flowVariables = { nome: 'Visitante' };
            const data = editor.export().drawflow.Home.data;
            const startNode = Object.values(data).find(n => n.name === 'start');
            if (startNode) runEngine(startNode, data);
        }

        /** Alinhado à lógica em FlowEngineService (filled / equals). */
        function evaluateFlowCondition(params, vars) {
            const p = params || {};
            const vname = String(p.var || '').trim();
            const op = p.op || 'filled';
            const val = p.val != null ? String(p.val) : '';
            const raw = vname ? vars[vname] : undefined;
            if (op === 'equals') {
                return String(raw ?? '').trim() === val.trim();
            }
            return raw !== undefined && raw !== null && String(raw).trim() !== '';
        }

        function runEngine(node, allNodes) {
            if (!node) return;
            if (node.name === 'start') {
                const nextId = node.outputs.output_1?.connections[0]?.node;
                if (nextId) runEngine(allNodes[nextId], allNodes);
                return;
            }
            setTimeout(() => {
                if (node.name === 'wait') {
                    const p = node.data.params || {};
                    let sec = parseFloat(p.seconds);
                    if (Number.isNaN(sec) || sec < 0) sec = 2;
                    sec = Math.min(sec, 15);
                    appendMsg('bot', 'Pausa de ' + sec + ' s (simulação; em produção o motor pode avançar sem espera real).');
                    setTimeout(() => {
                        const nextId = node.outputs.output_1?.connections[0]?.node;
                        if (nextId) runEngine(allNodes[nextId], allNodes);
                    }, Math.min(sec * 1000, 8000));
                    return;
                }
                if (node.name === 'condition') {
                    const p = node.data.params || {};
                    const ok = evaluateFlowCondition(p, flowVariables);
                    const outKey = ok ? 'output_1' : 'output_2';
                    const vname = String(p.var || '').trim();
                    appendMsg('bot', '[Simulador] Condição' + (vname ? ' «' + vname + '»' : '') + ': ' + (ok ? 'verdadeiro → saída 1' : 'falso → saída 2'));
                    const nextId = node.outputs[outKey]?.connections[0]?.node;
                    if (nextId) runEngine(allNodes[nextId], allNodes);
                    return;
                }
                if (node.name === 'message') {
                    const text = (node.data.params.text || '…').replace(/@{{(.*?)}}/g, (m, k) => flowVariables[k.trim()] || m);
                    appendMsg('bot', text);
                } else if (node.name === 'buttons') {
                    const text = (node.data.params.text || '…').replace(/@{{(.*?)}}/g, (m, k) => flowVariables[k.trim()] || m);
                    appendMsg('bot', text);
                    renderSimButtons(node, allNodes);
                    return;
                } else if (node.name === 'list') {
                    const p = node.data.params || {};
                    let block = (p.text || '…').replace(/@{{(.*?)}}/g, (m, k) => flowVariables[k.trim()] || m);
                    if (p.section_title) block += '\n\n' + p.section_title;
                    appendMsg('bot', block);
                    renderSimList(node, allNodes);
                    return;
                } else if (node.name === 'input') {
                    appendMsg('bot', node.data.params.label || '?');
                    window.pendingInputNode = node;
                    return;
                } else if (node.name === 'end') {
                    appendMsg('bot', 'Fim do fluxo.');
                    return;
                } else if (node.name === 'action') {
                    const actionLabels = { transfer: 'Transferir para humano', webhook: 'Webhook (POST)', close: 'Encerrar pedido' };
                    const t = node.data.params.type;
                    appendMsg('bot', 'Ação: ' + (actionLabels[t] || t || '—'));
                }

                const nextId = node.outputs.output_1?.connections[0]?.node;
                if (nextId) runEngine(allNodes[nextId], allNodes);
            }, 800);
        }

        function appendMsg(type, text) {
            const chat = document.getElementById('chat-messages');
            const isBot = type === 'bot';
            const div = document.createElement('div');
            div.className = `flex ${isBot ? 'justify-start' : 'justify-end'} animate-in slide-in-from-${isBot?'left':'right'}-4`;
            div.innerHTML = `<div class="${isBot?'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border shadow-md':'bg-indigo-600 text-white shadow-lg'} p-4 rounded-3xl ${isBot?'rounded-tl-none':'rounded-tr-none'} max-w-[85%] text-sm font-medium leading-relaxed whitespace-pre-wrap break-words">${text}</div>`;
            chat.appendChild(div);
            chat.scrollTo({ top: chat.scrollHeight, behavior: 'smooth' });
        }

        function renderSimButtons(node, data) {
            const container = document.getElementById('sim-options-container');
            container.innerHTML = '';
            [1,2,3].forEach(i => {
                const label = node.data.params['btn'+i];
                if (label) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = "bg-white dark:bg-gray-800 border-2 border-indigo-500 text-indigo-500 py-2 px-4 rounded-2xl text-xs font-bold hover:bg-indigo-500 hover:text-white transition-all";
                    btn.innerText = label;
                    btn.onclick = () => {
                        appendMsg('user', label);
                        container.innerHTML = '';
                        const next = node.outputs['output_'+i]?.connections[0]?.node;
                        if (next) runEngine(data[next], data);
                    };
                    container.appendChild(btn);
                }
            });
        }

        function renderSimList(node, data) {
            const container = document.getElementById('sim-options-container');
            container.innerHTML = '';
            [1,2,3].forEach(i => {
                const label = node.data.params['item'+i];
                if (label) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = "flex w-full items-center gap-2 border-l-4 border-teal-500 bg-white py-2 pl-3 pr-4 text-left text-xs font-semibold text-gray-800 shadow-sm transition hover:bg-teal-50 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-teal-900/20";
                    btn.innerHTML = `<span class="text-[10px] font-bold text-teal-600 dark:text-teal-400">${i}.</span><span>${label}</span>`;
                    btn.onclick = () => {
                        appendMsg('user', label);
                        container.innerHTML = '';
                        const next = node.outputs['output_'+i]?.connections[0]?.node;
                        if (next) runEngine(data[next], data);
                    };
                    container.appendChild(btn);
                }
            });
        }

        function sendSimulatedMessage() {
            const input = document.getElementById('chat-input');
            const val = input.value.trim();
            if (!val) return;
            appendMsg('user', val);
            input.value = '';
            if (window.pendingInputNode) {
                const node = window.pendingInputNode;
                if (node.data.params.variable) flowVariables[node.data.params.variable] = val;
                window.pendingInputNode = null;
                const next = node.outputs.output_1?.connections[0]?.node;
                if (next) runEngine(editor.export().drawflow.Home.data[next], editor.export().drawflow.Home.data);
            }
        }

        function closeSimulator() { document.getElementById('simulator-overlay').classList.replace('flex', 'hidden'); }
    </script>
    @endpush
</x-filament-panels::page>
