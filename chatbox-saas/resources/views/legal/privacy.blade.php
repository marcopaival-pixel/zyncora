@extends('layouts.public')

@section('title', 'Política de Privacidade - Zynkora')

@section('content')
<div class="container">
    <div class="legal-container">
        <h1 class="legal-title">Política de Privacidade</h1>
        <div class="legal-date">Última atualização: 08 de Junho de 2026</div>

        <div class="legal-content">
            <p>A Zynkora leva a sua privacidade a sério. Esta política descreve como coletamos, usamos, compartilhamos e protegemos seus dados pessoais em conformidade com a Lei Geral de Proteção de Dados (LGPD - Lei nº 13.709/2018).</p>

            <h2>1. Dados Coletados</h2>
            <p>Podemos coletar as seguintes categorias de dados durante o uso da plataforma:</p>
            <ul>
                <li>Nome completo.</li>
                <li>Endereço de e-mail corporativo ou pessoal.</li>
                <li>Número de telefone / WhatsApp.</li>
                <li>Dados da empresa (CNPJ, Razão Social).</li>
                <li>Logs de acesso (endereço IP, user-agent, data e hora de acesso).</li>
                <li>Conteúdo de conversas gerenciadas pelos agentes.</li>
                <li>Informações de faturamento e pagamento.</li>
            </ul>

            <h2>2. Finalidade da Coleta</h2>
            <p>A coleta destes dados tem as seguintes finalidades:</p>
            <ul>
                <li>Criação, identificação e autenticação da conta de usuário.</li>
                <li>Prestação adequada dos serviços de automação e chatbot.</li>
                <li>Processamento de cobranças e faturamento das assinaturas.</li>
                <li>Prestação de suporte técnico e atendimento ao cliente.</li>
                <li>Segurança da informação (prevenção a fraudes e incidentes).</li>
                <li>Melhorias contínuas das funcionalidades da plataforma.</li>
            </ul>

            <h2>3. Compartilhamento de Dados</h2>
            <p>A Zynkora não vende seus dados pessoais. Seus dados poderão ser compartilhados estritamente com serviços parceiros necessários para o funcionamento e operação da plataforma, tais como:</p>
            <ul>
                <li>Provedores de hospedagem em nuvem (Cloud).</li>
                <li>Serviços de banco de dados.</li>
                <li>Provedores de serviços de autenticação.</li>
                <li>Gateways e processadores de pagamento.</li>
                <li>Provedores de Inteligência Artificial (ex: APIs da OpenAI) estritamente para o processamento das conversas.</li>
            </ul>

            <h2>4. Segurança</h2>
            <p>Adotamos mecanismos técnicos, físicos e administrativos compatíveis com as melhores práticas do mercado para garantir a proteção de seus dados contra acesso não autorizado, destruição, perda, alteração ou qualquer forma de tratamento inadequado ou ilícito. Nossos dados em trânsito são protegidos por criptografia (SSL/TLS).</p>

            <h2>5. Direitos do Titular (LGPD)</h2>
            <p>Como titular dos dados pessoais, você tem o direito de:</p>
            <ul>
                <li>Confirmar a existência de tratamento de dados.</li>
                <li>Acesso aos dados de forma facilitada.</li>
                <li>Correção de dados incompletos, inexatos ou desatualizados.</li>
                <li>Exclusão (apagar dados processados sob o seu consentimento, salvo quando houver base legal para retenção).</li>
                <li>Portabilidade de seus dados a outro fornecedor de serviço.</li>
                <li>Revogação do consentimento previamente concedido.</li>
            </ul>

            <h2>6. Canal de Privacidade</h2>
            <p>Para exercer qualquer um de seus direitos ou tirar dúvidas sobre esta Política de Privacidade, acesse a nossa <a href="{{ route('legal.lgpd-central') }}" style="color: #10b981;">Central LGPD</a> ou entre em contato diretamente pelo e-mail:</p>
            <p><a href="mailto:privacidade@zynkora.com.br" style="color: #10b981; font-weight: bold;">privacidade@zynkora.com.br</a></p>
        </div>
    </div>
</div>
@endsection
