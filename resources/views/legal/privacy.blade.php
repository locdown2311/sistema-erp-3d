@extends('layouts.app')

@section('page-title', 'Política de Privacidade — Central3D')

@section('hide_sidebar', true)
@section('hide_header', true)

@section('content')

<div class="min-h-screen bg-zinc-50 dark:bg-[#09090b] text-zinc-900 dark:text-zinc-100 -mt-4 sm:-mt-6 lg:-mt-8 -mx-4 sm:-mx-6 lg:-mx-8 relative overflow-x-hidden transition-colors duration-300">

    <!-- Navbar -->
    <nav class="sticky top-0 z-40 px-4 sm:px-6 lg:px-8 w-full backdrop-blur-xl bg-white/70 dark:bg-[#09090b]/70 border-b border-zinc-200/50 dark:border-white/5 transition-colors duration-300">
        <div class="max-w-7xl mx-auto h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-xl font-black tracking-tight text-zinc-900 dark:text-white group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-emerald-500/25">
                    <i class="fas fa-cube text-base"></i>
                </div>
                Central<span class="text-emerald-500">3D</span>
            </a>
            <a href="{{ route('home') }}" class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 hover:text-emerald-500 dark:hover:text-emerald-400 transition-colors">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <!-- Header -->
        <div class="mb-12 text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-teal-50 dark:bg-teal-500/10 border border-teal-200 dark:border-teal-500/20 text-teal-600 dark:text-teal-400 text-xs font-bold uppercase tracking-wider mb-6">
                <i class="fas fa-shield-alt"></i> Documento Legal
            </div>
            <h1 class="text-4xl md:text-5xl font-black tracking-tight mb-4">Política de <span class="text-teal-500">Privacidade</span></h1>
            <p class="text-zinc-500 dark:text-zinc-400 text-sm">Última atualização: {{ date('d/m/Y') }}</p>
        </div>

        <!-- Content -->
        <div class="prose prose-zinc dark:prose-invert max-w-none space-y-8">

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-500/10 flex items-center justify-center text-teal-600 dark:text-teal-400 text-sm"><i class="fas fa-info-circle"></i></span>
                    1. Introdução
                </h2>
                <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed">
                    A <strong>Central3D</strong>, inscrita no CNPJ 65.490.660/0001-43 (MEI), valoriza a privacidade e a proteção dos dados pessoais de seus usuários. Esta Política de Privacidade descreve como coletamos, utilizamos, armazenamos e protegemos suas informações, em conformidade com a <strong>Lei Geral de Proteção de Dados (LGPD — Lei 13.709/2018)</strong>.
                </p>
            </section>

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-500/10 flex items-center justify-center text-teal-600 dark:text-teal-400 text-sm"><i class="fas fa-database"></i></span>
                    2. Dados Coletados
                </h2>
                <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed mb-3">Coletamos os seguintes dados pessoais:</p>
                <ul class="text-zinc-600 dark:text-zinc-300 leading-relaxed space-y-2 list-disc list-inside">
                    <li><strong>Dados de cadastro:</strong> nome, e-mail, telefone, endereço e CPF/CNPJ.</li>
                    <li><strong>Dados de navegação:</strong> endereço IP, tipo de navegador, páginas visitadas e cookies.</li>
                    <li><strong>Dados transacionais:</strong> informações de pedidos, pagamentos e histórico de compras.</li>
                    <li><strong>Dados de loja:</strong> nome da loja, logotipo, descrição e produtos cadastrados.</li>
                </ul>
            </section>

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-500/10 flex items-center justify-center text-teal-600 dark:text-teal-400 text-sm"><i class="fas fa-cogs"></i></span>
                    3. Finalidade do Uso dos Dados
                </h2>
                <ul class="text-zinc-600 dark:text-zinc-300 leading-relaxed space-y-2 list-disc list-inside">
                    <li>Viabilizar o funcionamento do marketplace e das lojas virtuais.</li>
                    <li>Processar transações e pagamentos.</li>
                    <li>Melhorar a experiência do usuário e personalizar conteúdo.</li>
                    <li>Enviar comunicações relevantes sobre a plataforma (com consentimento).</li>
                    <li>Cumprir obrigações legais e regulatórias.</li>
                </ul>
            </section>

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-500/10 flex items-center justify-center text-teal-600 dark:text-teal-400 text-sm"><i class="fas fa-share-alt"></i></span>
                    4. Compartilhamento de Dados
                </h2>
                <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed mb-3">Seus dados podem ser compartilhados apenas com:</p>
                <ul class="text-zinc-600 dark:text-zinc-300 leading-relaxed space-y-2 list-disc list-inside">
                    <li><strong>Processadores de pagamento:</strong> para efetuar cobranças (ex: Stripe).</li>
                    <li><strong>Serviços de hospedagem:</strong> para manter a plataforma em funcionamento.</li>
                    <li><strong>Autoridades legais:</strong> quando exigido por lei ou ordem judicial.</li>
                </ul>
                <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed mt-3">
                    <strong>Nunca vendemos</strong> seus dados pessoais a terceiros.
                </p>
            </section>

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-500/10 flex items-center justify-center text-teal-600 dark:text-teal-400 text-sm"><i class="fas fa-cookie-bite"></i></span>
                    5. Cookies
                </h2>
                <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed">
                    Utilizamos cookies essenciais para o funcionamento da plataforma (autenticação, preferências de tema) e cookies analíticos para entender o uso do site. Você pode desativar cookies nas configurações do seu navegador, mas isso pode afetar o funcionamento da plataforma.
                </p>
            </section>

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-500/10 flex items-center justify-center text-teal-600 dark:text-teal-400 text-sm"><i class="fas fa-user-shield"></i></span>
                    6. Seus Direitos (LGPD)
                </h2>
                <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed mb-3">Conforme a LGPD, você tem direito a:</p>
                <ul class="text-zinc-600 dark:text-zinc-300 leading-relaxed space-y-2 list-disc list-inside">
                    <li><strong>Confirmação e acesso:</strong> saber quais dados temos sobre você.</li>
                    <li><strong>Correção:</strong> solicitar a atualização de dados incorretos.</li>
                    <li><strong>Eliminação:</strong> solicitar a exclusão de dados pessoais.</li>
                    <li><strong>Portabilidade:</strong> solicitar a transferência de seus dados.</li>
                    <li><strong>Revogação do consentimento:</strong> retirar o consentimento a qualquer momento.</li>
                </ul>
                <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed mt-3">
                    Para exercer seus direitos, entre em contato pelo painel de configurações da sua conta ou envie uma solicitação à nossa equipe.
                </p>
            </section>

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-500/10 flex items-center justify-center text-teal-600 dark:text-teal-400 text-sm"><i class="fas fa-lock"></i></span>
                    7. Segurança dos Dados
                </h2>
                <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed">
                    Adotamos medidas técnicas e organizacionais para proteger seus dados, incluindo criptografia HTTPS, controle de acesso restrito, backups regulares e monitoramento contínuo. Nenhum sistema é 100% seguro, mas nos empenhamos constantemente para garantir a máxima proteção.
                </p>
            </section>

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-500/10 flex items-center justify-center text-teal-600 dark:text-teal-400 text-sm"><i class="fas fa-clock"></i></span>
                    8. Retenção de Dados
                </h2>
                <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed">
                    Seus dados pessoais são armazenados pelo tempo necessário para cumprir as finalidades descritas nesta política e para atender obrigações legais. Após solicitação de exclusão, os dados serão removidos em até 30 dias, exceto quando houver obrigação legal de mantê-los.
                </p>
            </section>

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-500/10 flex items-center justify-center text-teal-600 dark:text-teal-400 text-sm"><i class="fas fa-sync-alt"></i></span>
                    9. Alterações nesta Política
                </h2>
                <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed">
                    Esta política pode ser atualizada periodicamente. Notificaremos os usuários sobre mudanças significativas por meio da plataforma. A continuidade do uso após alterações implica na aceitação da versão atualizada.
                </p>
            </section>

        </div>

        <!-- Back link -->
        <div class="mt-12 text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-bold rounded-full hover:scale-105 transition-all duration-300">
                <i class="fas fa-arrow-left"></i> Voltar à Página Inicial
            </a>
        </div>
    </div>
</div>

@endsection
