@extends('layouts.public')

@section('title', 'Política de Cookies - Zynkora')

@section('content')
<div class="container">
    <div class="legal-container">
        <h1 class="legal-title">Política de Cookies</h1>
        <div class="legal-date">Última atualização: 08 de Junho de 2026</div>

        <div class="legal-content">
            <p>Utilizamos cookies e tecnologias semelhantes para melhorar a sua experiência em nossa plataforma, analisar como você interage com nossos serviços e personalizar conteúdos.</p>

            <h2>1. O que são Cookies?</h2>
            <p>Cookies são pequenos arquivos de texto que são armazenados no seu computador ou dispositivo móvel quando você visita um site ou aplicativo. Eles ajudam a plataforma a lembrar de informações sobre sua visita, como idioma, autenticação e outras configurações.</p>

            <h2>2. Categorias de Cookies Utilizados</h2>
            
            <h3>Cookies Necessários</h3>
            <p>Estes cookies são essenciais para que o sistema funcione corretamente e não podem ser desativados. Eles geralmente são definidos apenas em resposta a ações feitas por você, como fazer login (autenticação), preencher formulários ou definir preferências de privacidade.</p>
            <ul>
                <li>Sessão de usuário autenticado (Filament).</li>
                <li>Tokens CSRF para segurança de formulários.</li>
            </ul>

            <h3>Cookies Funcionais</h3>
            <p>Estes cookies permitem que o site forneça funcionalidades e personalização aprimoradas. Podem ser definidos por nós ou por fornecedores terceiros cujos serviços adicionamos às nossas páginas.</p>

            <h3>Cookies Analíticos</h3>
            <p>Estes cookies nos ajudam a entender como os visitantes interagem com o nosso site, coletando e relatando informações de forma anônima. Eles nos permitem contar visitas e fontes de tráfego, para que possamos medir e melhorar o desempenho da plataforma.</p>

            <h3>Cookies de Marketing</h3>
            <p>Quando aplicável, estes cookies são usados para rastrear visitantes em sites com o objetivo de exibir anúncios relevantes. Atualmente a Zynkora não compartilha dados com redes de publicidade de terceiros por meio destes cookies na área interna do software.</p>

            <h2>3. Consentimento e Gerenciamento</h2>
            <p>Você pode configurar o seu navegador para recusar cookies ou alertá-lo quando eles estiverem sendo enviados. Contudo, partes da plataforma (como a área logada) poderão não funcionar adequadamente caso bloqueie os cookies Necessários.</p>
            <p>A qualquer momento, você pode gerenciar suas preferências através das configurações do seu navegador de internet ou em nossos painéis de controle, onde disponibilizaremos opções de revisão de permissões concedidas.</p>
        </div>
    </div>
</div>
@endsection
