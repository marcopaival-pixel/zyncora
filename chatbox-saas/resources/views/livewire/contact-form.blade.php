<div>
    @if($success)
        <div style="background-color: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; padding: 2rem; border-radius: 8px; margin-bottom: 1rem; text-align: center;">
            <i data-lucide="check-circle" style="width: 48px; height: 48px; margin: 0 auto 1rem;"></i>
            <h3 style="margin: 0 0 0.5rem; font-weight: bold; font-size: 1.5rem;">
                {{ $settings->success_message_title ?? 'Recebemos sua solicitação com sucesso!' }}
            </h3>
            <p style="margin: 0 0 1.5rem; font-size: 1rem; opacity: 0.9;">
                {{ $settings->success_message_subtitle ?? 'Nossa equipe entrará em contato em breve.' }}
            </p>
            
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="#pricing" class="btn-secondary" style="border-color: #10b981; color: #10b981;">Ver Planos</a>
                @if(Route::has('demo'))
                <a href="/demo" class="btn-secondary" style="border-color: #10b981; color: #10b981;">Ver Demonstração</a>
                @endif
                <a href="/admin/register" class="btn-primary" style="background-color: #10b981;">Criar Conta Gratuita</a>
            </div>
        </div>
    @else
        <form wire:submit.prevent="submit" id="advanced-contact-form">
            <!-- Hidden Tracking Fields -->
            <input type="hidden" wire:model="utm_source" id="utm_source">
            <input type="hidden" wire:model="utm_medium" id="utm_medium">
            <input type="hidden" wire:model="utm_campaign" id="utm_campaign">
            <input type="hidden" wire:model="utm_content" id="utm_content">
            <input type="hidden" wire:model="utm_term" id="utm_term">
            <input type="hidden" wire:model="referer" id="referer">
            <input type="hidden" wire:model="browser" id="browser">
            <input type="hidden" wire:model="device" id="device">
            <input type="hidden" wire:model="os" id="os">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Nome Completo *</label>
                    <input type="text" wire:model="name" class="form-control" placeholder="Seu nome completo" required>
                    @error('name') <span style="color: #ef4444; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>
                
                <div class="form-group">
                    <label>Empresa *</label>
                    <input type="text" wire:model="company" class="form-control" placeholder="Nome da empresa" required>
                    @error('company') <span style="color: #ef4444; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>
                
                <div class="form-group">
                    <label>Cargo</label>
                    <input type="text" wire:model="role" class="form-control" placeholder="Seu cargo">
                    @error('role') <span style="color: #ef4444; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>E-mail Corporativo *</label>
                    <input type="email" wire:model="email" class="form-control" placeholder="seuemail@empresa.com" required>
                    @error('email') <span style="color: #ef4444; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>
                
                <div class="form-group">
                    <label>WhatsApp *</label>
                    <input type="text" wire:model="whatsapp" class="form-control" placeholder="(00) 00000-0000" required>
                    @error('whatsapp') <span style="color: #ef4444; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>
                
                <div class="form-group">
                    <label>Quantidade de Atendentes</label>
                    <input type="number" wire:model="attendants_qty" class="form-control" placeholder="Ex: 5" min="1">
                    @error('attendants_qty') <span style="color: #ef4444; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>
                
                <div class="form-group">
                    <label>Segmento</label>
                    <select wire:model="segment" class="form-control" style="background-color: rgba(255, 255, 255, 0.05); color: white;">
                        <option value="" style="color: black;">Selecione o segmento</option>
                        <option value="Clínicas e Saúde" style="color: black;">Clínicas e Saúde</option>
                        <option value="E-commerce" style="color: black;">E-commerce</option>
                        <option value="Serviços" style="color: black;">Serviços</option>
                        <option value="Tecnologia" style="color: black;">Tecnologia</option>
                        <option value="Varejo" style="color: black;">Varejo</option>
                        <option value="Outros" style="color: black;">Outros</option>
                    </select>
                    @error('segment') <span style="color: #ef4444; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label>Mensagem (Opcional)</label>
                    <textarea wire:model="message" class="form-control" rows="3" placeholder="Como podemos ajudar?"></textarea>
                    @error('message') <span style="color: #ef4444; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <button type="submit" class="btn-primary" style="width: 100%; border: none; cursor: pointer; position: relative; margin-top: 1rem; padding: 1rem; font-size: 1.1rem;">
                <span wire:loading.remove wire:target="submit">Solicitar Demonstração</span>
                <span wire:loading wire:target="submit">Enviando...</span>
            </button>
        </form>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            const urlParams = new URLSearchParams(window.location.search);
            @this.set('utm_source', urlParams.get('utm_source') || '');
            @this.set('utm_medium', urlParams.get('utm_medium') || '');
            @this.set('utm_campaign', urlParams.get('utm_campaign') || '');
            @this.set('utm_content', urlParams.get('utm_content') || '');
            @this.set('utm_term', urlParams.get('utm_term') || '');
            @this.set('referer', document.referrer || '');
            
            const ua = navigator.userAgent;
            let browser = 'Unknown';
            if(ua.includes('Chrome')) browser = 'Chrome';
            else if(ua.includes('Firefox')) browser = 'Firefox';
            else if(ua.includes('Safari')) browser = 'Safari';
            
            let os = 'Unknown';
            if(ua.includes('Win')) os = 'Windows';
            else if(ua.includes('Mac')) os = 'MacOS';
            else if(ua.includes('Linux')) os = 'Linux';
            else if(ua.includes('Android')) os = 'Android';
            else if(ua.includes('iOS') || ua.includes('iPhone')) os = 'iOS';
            
            @this.set('browser', browser);
            @this.set('os', os);
            @this.set('device', window.innerWidth < 768 ? 'Mobile' : 'Desktop');
        });
    </script>
</div>
