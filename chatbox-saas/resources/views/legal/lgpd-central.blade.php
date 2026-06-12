@extends('layouts.public')

@section('title', 'Central LGPD - Zynkora')

@section('content')
<div class="container">
    <div class="legal-container">
        <h1 class="legal-title">Central LGPD - Direitos do Titular</h1>
        <div class="legal-date">Plataforma Zynkora</div>

        <div class="legal-content">
            <p>Nós da Zynkora levamos seus dados muito a sério. Conforme as diretrizes da Lei Geral de Proteção de Dados Pessoais (LGPD), você pode exercer os seus direitos preenchendo o formulário abaixo.</p>
            
            <p>Caso tenha dúvidas, consulte a nossa <a href="{{ route('legal.privacy') }}" style="color: #10b981;">Política de Privacidade</a>.</p>

            @if(session('success'))
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; padding: 15px; border-radius: 8px; margin-top: 20px; margin-bottom: 20px;">
                    <strong>Pronto!</strong> {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('legal.lgpd-request.submit') }}" method="POST" style="margin-top: 30px; background: rgba(0,0,0,0.2); padding: 30px; border-radius: 12px;">
                @csrf
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="name" style="color: white; display: block; margin-bottom: 5px;">Nome Completo</label>
                    <input type="text" id="name" name="name" class="form-control" required placeholder="Seu nome completo">
                    @error('name')<span style="color: #f87171; font-size: 0.85rem;">{{ $message }}</span>@enderror
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="email" style="color: white; display: block; margin-bottom: 5px;">E-mail</label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="E-mail associado à conta">
                    @error('email')<span style="color: #f87171; font-size: 0.85rem;">{{ $message }}</span>@enderror
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="request_type" style="color: white; display: block; margin-bottom: 5px;">Tipo de Solicitação</label>
                    <select id="request_type" name="request_type" class="form-control" required>
                        <option value="">-- Selecione uma opção --</option>
                        <option value="access">Solicitar acesso aos dados</option>
                        <option value="correction">Solicitar correção de dados</option>
                        <option value="deletion">Solicitar exclusão de dados</option>
                        <option value="portability">Solicitar portabilidade</option>
                        <option value="revoke">Revogar consentimento prévio</option>
                    </select>
                    @error('request_type')<span style="color: #f87171; font-size: 0.85rem;">{{ $message }}</span>@enderror
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="details" style="color: white; display: block; margin-bottom: 5px;">Detalhes Adicionais (Opcional)</label>
                    <textarea id="details" name="details" class="form-control" rows="4" placeholder="Adicione qualquer informação relevante para a sua solicitação..."></textarea>
                    @error('details')<span style="color: #f87171; font-size: 0.85rem;">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; border: none; cursor: pointer; padding: 15px; font-size: 1.1rem;">
                    Abrir Protocolo LGPD
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
