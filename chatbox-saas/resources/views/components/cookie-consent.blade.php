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
        transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    #cookie-preferences-modal {
        transition: opacity 0.3s ease-out, transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .backdrop-blur-xl {
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
    }
    .glass-effect {
        background: rgba(9, 9, 11, 0.85); /* zinc-950 */
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    
    /* Custom Toggle Switch CSS */
    .toggle-checkbox:checked {
        right: 0;
        border-color: #18181b; /* zinc-900 */
    }
    .toggle-checkbox:checked + .toggle-label {
        background-color: #e4e4e7; /* zinc-200 */
    }
    .toggle-checkbox:checked + .toggle-label:after {
        transform: translateX(100%);
        background-color: #18181b; /* zinc-900 */
    }
    .toggle-label {
        width: 40px;
        height: 24px;
        background-color: #27272a; /* zinc-800 */
        border-radius: 9999px;
        position: relative;
        cursor: pointer;
        transition: background-color 0.3s ease;
        border: 1px solid #3f3f46; /* zinc-700 */
    }
    .toggle-label:after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 18px;
        height: 18px;
        background-color: #71717a; /* zinc-500 */
        border-radius: 50%;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
</style>

<!-- Banner Principal -->
<div id="cookie-consent-banner" class="fixed bottom-4 left-4 right-4 md:bottom-8 md:left-auto md:right-8 md:w-[420px] z-[9999] transform translate-y-full opacity-0 pointer-events-none" style="font-family: 'Outfit', sans-serif;">
    <div class="glass-effect p-6 rounded-2xl relative overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-tr from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
        
        <div class="flex items-start gap-4 relative z-10">
            <div class="bg-zinc-900/80 p-2.5 rounded-full border border-zinc-700/50 shadow-inner shrink-0 mt-1">
                <i data-lucide="shield" class="w-5 h-5 text-zinc-300"></i>
            </div>
            <div>
                <h3 class="text-zinc-100 font-semibold text-sm mb-1 tracking-tight">Privacidade Zyncora</h3>
                <p class="text-zinc-400 text-xs leading-relaxed mb-4">
                    Utilizamos cookies para aprimorar sua experiência, analisar nosso tráfego e personalizar conteúdo. 
                    Ao clicar em "Aceitar Tudo", você concorda com o uso de todos os cookies. Saiba mais em nossa 
                    <a href="{{ route('privacy') }}" class="text-zinc-300 hover:text-white underline decoration-zinc-600 underline-offset-2 transition-colors">
                        Política de Privacidade
                    </a>.
                </p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-2 mt-2 relative z-10">
            <button onclick="acceptAllCookies()" class="w-full sm:flex-1 bg-white text-zinc-950 text-xs font-semibold py-2.5 px-4 rounded-xl hover:bg-zinc-200 transition-all shadow-sm">
                Aceitar Tudo
            </button>
            <button onclick="rejectAllCookies()" class="w-full sm:flex-1 bg-zinc-900 border border-zinc-700 text-zinc-300 text-xs font-medium py-2.5 px-4 rounded-xl hover:bg-zinc-800 hover:text-white transition-all">
                Recusar Tudo
            </button>
            <button onclick="openCookiePreferences()" class="w-full sm:w-auto bg-transparent text-zinc-400 p-2.5 rounded-xl hover:bg-zinc-800/80 hover:text-zinc-300 transition-all flex items-center justify-center border border-transparent hover:border-zinc-700" aria-label="Preferências Avançadas">
                <i data-lucide="settings" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal de Preferências -->
<div id="cookie-preferences-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[10000] opacity-0 pointer-events-none transition-opacity duration-300 flex items-center justify-center p-4" style="font-family: 'Outfit', sans-serif;">
    <div id="cookie-preferences-modal" class="bg-zinc-950 border border-zinc-800 w-full max-w-md rounded-2xl shadow-2xl relative overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
        
        <div class="flex items-center justify-between p-5 border-b border-zinc-800/80 bg-zinc-900/30">
            <h3 class="text-zinc-100 font-semibold">Preferências de Privacidade</h3>
            <button onclick="closeCookiePreferences()" class="text-zinc-400 hover:text-zinc-100 transition-colors p-1 rounded-md hover:bg-zinc-800/80">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <div class="p-5 max-h-[60vh] overflow-y-auto space-y-4">
            <!-- Essenciais -->
            <div class="flex items-start justify-between p-4 rounded-xl bg-zinc-900/50 border border-zinc-800/80">
                <div class="pr-4">
                    <h4 class="text-zinc-200 text-sm font-medium mb-1">Cookies Essenciais</h4>
                    <p class="text-zinc-500 text-xs">Obrigatórios para o funcionamento da plataforma. Incluem segurança, rede e acessibilidade.</p>
                </div>
                <div class="shrink-0 flex items-center h-5 mt-1">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider bg-zinc-800 px-2 py-1 rounded border border-zinc-700">Sempre Ativo</span>
                </div>
            </div>

            <!-- Analytics -->
            <div class="flex items-start justify-between p-4 rounded-xl hover:bg-zinc-900/30 transition-colors border border-transparent hover:border-zinc-800/50 cursor-pointer" onclick="toggleCheckbox('pref-analytics')">
                <div class="pr-4 pointer-events-none">
                    <h4 class="text-zinc-200 text-sm font-medium mb-1">Estatísticas & Analytics</h4>
                    <p class="text-zinc-500 text-xs">Ajudam-nos a entender como os visitantes interagem com o site, coletando dados de forma anônima.</p>
                </div>
                <div class="shrink-0 flex items-center mt-1">
                    <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" id="pref-analytics" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer opacity-0 z-10" />
                        <label for="pref-analytics" class="toggle-label block overflow-hidden h-6 rounded-full bg-zinc-800 cursor-pointer"></label>
                    </div>
                </div>
            </div>

            <!-- Marketing -->
            <div class="flex items-start justify-between p-4 rounded-xl hover:bg-zinc-900/30 transition-colors border border-transparent hover:border-zinc-800/50 cursor-pointer" onclick="toggleCheckbox('pref-marketing')">
                <div class="pr-4 pointer-events-none">
                    <h4 class="text-zinc-200 text-sm font-medium mb-1">Marketing & Ads</h4>
                    <p class="text-zinc-500 text-xs">Utilizados para fornecer anúncios mais relevantes e medir o sucesso de campanhas.</p>
                </div>
                <div class="shrink-0 flex items-center mt-1">
                    <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" id="pref-marketing" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer opacity-0 z-10" />
                        <label for="pref-marketing" class="toggle-label block overflow-hidden h-6 rounded-full bg-zinc-800 cursor-pointer"></label>
                    </div>
                </div>
            </div>

            <!-- Personalização -->
            <div class="flex items-start justify-between p-4 rounded-xl hover:bg-zinc-900/30 transition-colors border border-transparent hover:border-zinc-800/50 cursor-pointer" onclick="toggleCheckbox('pref-personalization')">
                <div class="pr-4 pointer-events-none">
                    <h4 class="text-zinc-200 text-sm font-medium mb-1">Personalização</h4>
                    <p class="text-zinc-500 text-xs">Permitem que o site lembre de escolhas que você faz, como idioma ou região.</p>
                </div>
                <div class="shrink-0 flex items-center mt-1">
                    <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" id="pref-personalization" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer opacity-0 z-10" />
                        <label for="pref-personalization" class="toggle-label block overflow-hidden h-6 rounded-full bg-zinc-800 cursor-pointer"></label>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-5 border-t border-zinc-800/80 bg-zinc-900/40 flex gap-3">
            <button onclick="savePreferences()" class="flex-1 bg-white text-zinc-950 text-sm font-semibold py-2.5 px-4 rounded-xl hover:bg-zinc-200 transition-all shadow-sm">
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
