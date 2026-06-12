@extends('layouts.public')

@section('title', 'Aceite de Termos - Zynkora')

@section('content')
<div class="container">
    <div class="legal-container" style="text-align: center;">
        <div style="margin-bottom: 20px; color: #10b981;">
            <i data-lucide="shield-check" style="width: 64px; height: 64px; margin: 0 auto;"></i>
        </div>
        <h1 class="legal-title" style="margin-bottom: 10px;">Atualização de Termos</h1>
        <div class="legal-content">
            <p style="margin-bottom: 30px;">Olá! Para continuarmos garantindo a sua segurança e a conformidade da nossa plataforma, publicamos novas versões dos nossos termos legais. Por favor, leia e aceite os documentos abaixo para continuar utilizando a Zynkora.</p>

            <form action="{{ route('legal.accept-pending') }}" method="POST" style="text-align: left; background: rgba(0,0,0,0.2); padding: 30px; border-radius: 12px; max-width: 600px; margin: 0 auto;">
                @csrf
                
                @foreach($pendingDocuments as $doc)
                    <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <label style="display: flex; align-items: flex-start; gap: 15px; cursor: pointer;">
                            <input type="hidden" name="documents[]" value="{{ $doc->id }}">
                            <input type="checkbox" required style="margin-top: 5px; width: 20px; height: 20px; accent-color: #10b981;">
                            <span style="color: white; font-size: 1.1rem; line-height: 1.4;">
                                Li e concordo com os 
                                @if($doc->type === 'terms')
                                    <a href="{{ route('legal.terms') }}" target="_blank" style="color: #10b981; text-decoration: underline;">Termos de Uso</a>
                                @elseif($doc->type === 'privacy')
                                    <a href="{{ route('legal.privacy') }}" target="_blank" style="color: #10b981; text-decoration: underline;">Política de Privacidade</a>
                                @elseif($doc->type === 'cookies')
                                    <a href="{{ route('legal.cookies') }}" target="_blank" style="color: #10b981; text-decoration: underline;">Política de Cookies</a>
                                @endif
                                (Versão {{ $doc->version }})
                            </span>
                        </label>
                    </div>
                @endforeach

                <button type="submit" class="btn-primary" style="width: 100%; border: none; cursor: pointer; padding: 15px; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <i data-lucide="check"></i> Confirmar Aceite
                </button>
            </form>
            
            <form action="{{ route('filament.admin.auth.logout') }}" method="POST" style="margin-top: 20px;">
                @csrf
                <button type="submit" style="background: none; border: none; color: rgba(255,255,255,0.5); text-decoration: underline; cursor: pointer; font-family: 'Outfit', sans-serif;">
                    Sair da conta
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
