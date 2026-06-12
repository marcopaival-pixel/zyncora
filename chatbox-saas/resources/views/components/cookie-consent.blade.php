<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: {
            preflight: false,
        }
    }
</script>
<style>
    /* Transições e Blur */
    #cookie-consent-banner {
        transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.6s cubic-bezier(0.22, 1, 0.36, 1);
    }
    #cookie-preferences-modal {
        transition: opacity 0.4s ease-out, transform 0.4s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .backdrop-blur-xl {
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
    }
    
    /* Efeito Glass Premium */
    .glass-effect {
        background: linear-gradient(135deg, rgba(24, 24, 27, 0.8) 0%, rgba(9, 9, 11, 0.95) 100%);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), 
                    inset 0 1px 1px rgba(255, 255, 255, 0.08),
                    inset 0 -1px 1px rgba(0, 0, 0, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
    }
    
    /* Custom Toggle Switch CSS */
    .toggle-checkbox:checked {
        right: 0;
        border-color: #18181b; 
    }
    .toggle-checkbox:checked + .toggle-label {
        background-color: #f4f4f5; /* zinc-100 */
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
    }
    .toggle-checkbox:checked + .toggle-label:after {
        transform: translateX(100%);
        background-color: #09090b; /* zinc-950 */
        border-color: #09090b;
    }
    .toggle-label {
        width: 44px;
        height: 24px;
        background-color: #18181b; /* zinc-900 */
        border-radius: 9999px;
        position: relative;
        cursor: pointer;
        transition: background-color 0.3s ease;
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
    }
    .toggle-label:after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 18px;
        height: 18px;
        background-color: #a1a1aa; /* zinc-400 */
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 50%;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
</style>

<!-- Banner Principal -->
<div id="cookie-consent-banner" class="fixed bottom-4 left-4 right-4 md:bottom-8 md:left-auto md:right-8 md:w-[440px] z-[9999] transform translate-y-full opacity-0 pointer-events-none" style="font-family: 'Outfit', sans-serif;">
    <div class="glass-effect p-7 rounded-3xl relative overflow-hidden group">
        <!-- Glow Effect no Background -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-zinc-100/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-zinc-100/5 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="absolute inset-0 bg-gradient-to-tr from-white/[0.02] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"></div>
        
        <div class="flex items-start gap-5 relative z-10">
            <div class="relative shrink-0 mt-1">
                <div class="absolute inset-0 bg-zinc-400/20 blur-xl rounded-full"></div>
                <div class="bg-gradient-to-b from-zinc-800 to-zinc-900 p-3 rounded-2xl border border-zinc-700/50 shadow-lg shadow-black/40 relative z-10 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-5 h-5 text-zinc-100"></i>
                </div>
            </div>
            <div>
                <h3 class="text-zinc-50 font-medium text-base mb-1.5 tracking-tight flex items-center gap-2">
                    Sua Privacidade
                </h3>
                <p class="text-zinc-400 text-sm leading-relaxed mb-6 font-light">
                    Utilizamos cookies para aprimorar sua experiência e analisar nosso tráfego. 
                    Ao clicar em "Aceitar Tudo", você concorda com nossa
                    <a href="{{ route('legal.privacy') }}" class="text-zinc-200 hover:text-white underline decoration-zinc-600/50 hover:decoration-zinc-400 underline-offset-4 transition-all duration-300">
                        Política de Privacidade
                    </a>.
                </p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-2.5 relative z-10">
            <button onclick="acceptAllCookies()" class="w-full sm:flex-1 bg-gradient-to-b from-white to-zinc-200 text-zinc-950 text-sm font-semibold py-3 px-5 rounded-xl hover:from-zinc-100 hover:to-zinc-300 transition-all shadow-[0_1px_2px_rgba(0,0,0,0.1),inset_0_1px_1px_rgba(255,255,255,0.8)] focus:ring-2 focus:ring-white/20 focus:outline-none">
                Aceitar Tudo
            </button>
            <button onclick="rejectAllCookies()" class="w-full sm:flex-1 bg-zinc-900/60 border border-zinc-700/50 text-zinc-300 text-sm font-medium py-3 px-5 rounded-xl hover:bg-zinc-800 hover:text-white transition-all shadow-sm focus:ring-2 focus:ring-zinc-700 focus:outline-none">
                Recusar Tudo
            </button>
            <button onclick="openCookiePreferences()" class="w-full sm:w-auto bg-zinc-900/30 text-zinc-400 p-3 rounded-xl hover:bg-zinc-800 hover:text-zinc-200 transition-all flex items-center justify-center border border-zinc-800/50 hover:border-zinc-600 group" aria-label="Preferências Avançadas">
                <i data-lucide="sliders-horizontal" class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal de Preferências -->
<div id="cookie-preferences-backdrop" class="fixed inset-0 bg-black/80 backdrop-blur-md z-[10000] opacity-0 pointer-events-none transition-opacity duration-500 flex items-center justify-center p-4" style="font-family: 'Outfit', sans-serif;">
    <div id="cookie-preferences-modal" class="bg-zinc-950/95 border border-zinc-800/80 w-full max-w-md rounded-3xl shadow-2xl relative overflow-hidden transform scale-95 opacity-0 transition-all duration-500 glass-effect">
        
        <div class="flex items-center justify-between p-6 border-b border-zinc-800/60 bg-zinc-900/20">
            <h3 class="text-zinc-50 font-medium text-lg flex items-center gap-2">
                <i data-lucide="settings-2" class="w-5 h-5 text-zinc-400"></i>
                Preferências de Privacidade
            </h3>
            <button onclick="closeCookiePreferences()" class="text-zinc-500 hover:text-zinc-100 transition-colors p-1.5 rounded-lg hover:bg-zinc-800/80 border border-transparent hover:border-zinc-700/50">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <div class="p-6 max-h-[60vh] overflow-y-auto space-y-3 custom-scrollbar">
            <!-- Essenciais -->
            <div class="flex items-start justify-between p-5 rounded-2xl bg-zinc-900/40 border border-zinc-800/80 shadow-inner">
                <div class="pr-5">
                    <h4 class="text-zinc-100 text-sm font-medium mb-1.5">Cookies Essenciais</h4>
                    <p class="text-zinc-500 text-xs leading-relaxed">Obrigatórios para o funcionamento da plataforma. Incluem segurança, rede e acessibilidade.</p>
                </div>
                <div class="shrink-0 flex items-center h-5 mt-1">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest bg-zinc-900 px-2.5 py-1.5 rounded-lg border border-zinc-800 shadow-sm">Sempre Ativo</span>
                </div>
            </div>

            <!-- Analytics -->
            <div class="flex items-start justify-between p-5 rounded-2xl hover:bg-zinc-900/40 transition-colors border border-transparent hover:border-zinc-800/50 cursor-pointer group" onclick="toggleCheckbox('pref-analytics')">
                <div class="pr-5 pointer-events-none">
                    <h4 class="text-zinc-200 text-sm font-medium mb-1.5 group-hover:text-zinc-100 transition-colors">Estatísticas & Analytics</h4>
                    <p class="text-zinc-500 text-xs leading-relaxed group-hover:text-zinc-400 transition-colors">Ajudam-nos a entender como os visitantes interagem com o site, coletando dados de forma anônima.</p>
                </div>
                <div class="shrink-0 flex items-center mt-1">
                    <div class="relative inline-block w-11 mr-0 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" id="pref-analytics" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer opacity-0 z-10" />
                        <label for="pref-analytics" class="toggle-label block overflow-hidden h-6 rounded-full bg-zinc-800 cursor-pointer"></label>
                    </div>
                </div>
            </div>

            <!-- Marketing -->
            <div class="flex items-start justify-between p-5 rounded-2xl hover:bg-zinc-900/40 transition-colors border border-transparent hover:border-zinc-800/50 cursor-pointer group" onclick="toggleCheckbox('pref-marketing')">
                <div class="pr-5 pointer-events-none">
                    <h4 class="text-zinc-200 text-sm font-medium mb-1.5 group-hover:text-zinc-100 transition-colors">Marketing & Ads</h4>
                    <p class="text-zinc-500 text-xs leading-relaxed group-hover:text-zinc-400 transition-colors">Utilizados para fornecer anúncios mais relevantes e medir o sucesso de campanhas.</p>
                </div>
                <div class="shrink-0 flex items-center mt-1">
                    <div class="relative inline-block w-11 mr-0 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" id="pref-marketing" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer opacity-0 z-10" />
                        <label for="pref-marketing" class="toggle-label block overflow-hidden h-6 rounded-full bg-zinc-800 cursor-pointer"></label>
                    </div>
                </div>
            </div>

            <!-- Personalização -->
            <div class="flex items-start justify-between p-5 rounded-2xl hover:bg-zinc-900/40 transition-colors border border-transparent hover:border-zinc-800/50 cursor-pointer group" onclick="toggleCheckbox('pref-personalization')">
                <div class="pr-5 pointer-events-none">
                    <h4 class="text-zinc-200 text-sm font-medium mb-1.5 group-hover:text-zinc-100 transition-colors">Personalização</h4>
                    <p class="text-zinc-500 text-xs leading-relaxed group-hover:text-zinc-400 transition-colors">Permitem que o site lembre de escolhas que você faz, como idioma ou região.</p>
                </div>
                <div class="shrink-0 flex items-center mt-1">
                    <div class="relative inline-block w-11 mr-0 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" id="pref-personalization" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer opacity-0 z-10" />
                        <label for="pref-personalization" class="toggle-label block overflow-hidden h-6 rounded-full bg-zinc-800 cursor-pointer"></label>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 border-t border-zinc-800/60 bg-zinc-900/20 flex gap-3">
            <button onclick="savePreferences()" class="flex-1 bg-gradient-to-b from-white to-zinc-200 text-zinc-950 text-sm font-semibold py-3 px-5 rounded-xl hover:from-zinc-100 hover:to-zinc-300 transition-all shadow-[0_1px_2px_rgba(0,0,0,0.1),inset_0_1px_1px_rgba(255,255,255,0.8)] focus:ring-2 focus:ring-white/20 focus:outline-none">
                Salvar Preferências
            </button>
        </div>
    </div>
</div>

<script>
    const COOKIE_KEY = 'zyncora-cookie-consent';

    document.addEventListener('DOMContentLoaded', () => {
        const storedConsent = localStorage.getItem(COOKIE_KEY);
        if (!storedConsent) {
            // Mostrar banner com leve atraso para a animação
            setTimeout(() => {
                const banner = document.getElementById('cookie-consent-banner');
                banner.classList.remove('translate-y-full', 'opacity-0', 'pointer-events-none');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }, 500);
        } else {
            // Disparar evento para ativar scripts conforme as preferências salvas
            try {
                const prefs = JSON.parse(storedConsent).preferences;
                window.dispatchEvent(new CustomEvent('cookie-consent-loaded', { detail: prefs }));
            } catch (e) {}
        }
    });

    function hideBanner() {
        const banner = document.getElementById('cookie-consent-banner');
        banner.classList.add('translate-y-full', 'opacity-0');
        setTimeout(() => banner.style.display = 'none', 500);
    }

    function saveConsent(preferences) {
        const data = {
            hasConsented: true,
            timestamp: new Date().toISOString(),
            preferences: {
                essential: true,
                ...preferences
            }
        };
        localStorage.setItem(COOKIE_KEY, JSON.stringify(data));
        window.dispatchEvent(new CustomEvent('cookie-consent-updated', { detail: data.preferences }));
        hideBanner();
        closeCookiePreferences();
    }

    function acceptAllCookies() {
        saveConsent({ analytics: true, marketing: true, personalization: true });
    }

    function rejectAllCookies() {
        saveConsent({ analytics: false, marketing: false, personalization: false });
    }

    function toggleCheckbox(id) {
        const checkbox = document.getElementById(id);
        checkbox.checked = !checkbox.checked;
    }

    function openCookiePreferences() {
        // Carregar valores locais para os checkboxes
        try {
            const stored = JSON.parse(localStorage.getItem(COOKIE_KEY));
            if (stored && stored.preferences) {
                document.getElementById('pref-analytics').checked = stored.preferences.analytics;
                document.getElementById('pref-marketing').checked = stored.preferences.marketing;
                document.getElementById('pref-personalization').checked = stored.preferences.personalization;
            }
        } catch (e) {}
        
        const backdrop = document.getElementById('cookie-preferences-backdrop');
        const modal = document.getElementById('cookie-preferences-modal');
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.remove('scale-95', 'opacity-0');
    }

    function closeCookiePreferences() {
        const backdrop = document.getElementById('cookie-preferences-backdrop');
        const modal = document.getElementById('cookie-preferences-modal');
        modal.classList.add('scale-95', 'opacity-0');
        backdrop.classList.add('opacity-0');
        setTimeout(() => {
            backdrop.classList.add('pointer-events-none');
        }, 300);
    }

    function savePreferences() {
        saveConsent({
            analytics: document.getElementById('pref-analytics').checked,
            marketing: document.getElementById('pref-marketing').checked,
            personalization: document.getElementById('pref-personalization').checked
        });
    }
</script>
