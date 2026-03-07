@extends('layouts.app')

@section('page-title', 'Termos de Uso — Central3D')

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
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold uppercase tracking-wider mb-6">
                <i class="fas fa-file-contract"></i> Documento Legal
            </div>
            <h1 class="text-4xl md:text-5xl font-black tracking-tight mb-4">Termos de <span class="text-emerald-500">Uso</span></h1>
            <p class="text-zinc-500 dark:text-zinc-400 text-sm">Última atualização: {{ date('d/m/Y') }}</p>
        </div>

        <!-- Content -->
        <div class="prose prose-zinc dark:prose-invert max-w-none space-y-8">

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-sm"><i class="fas fa-info-circle"></i></span>
                    1. Informações Gerais
                </h2>
                <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed">
                    A plataforma <strong>Central3D</strong> é um marketplace voltado para a comunidade de impressão 3D, operado sob o CNPJ 65.490.660/0001-43 (MEI). Ao utilizar nossos serviços, você concorda integralmente com estes Termos de Uso. Caso não concorde, recomendamos que não utilize a plataforma.
                </p>
            </section>

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-sm"><i class="fas fa-user-check"></i></span>
                    2. Cadastro e Conta
                </h2>
                <ul class="text-zinc-600 dark:text-zinc-300 leading-relaxed space-y-2 list-disc list-inside">
                    <li>O usuário deve fornecer informações verdadeiras, completas e atualizadas no ato do cadastro.</li>
                    <li>A conta é pessoal e intransferível. O usuário é responsável pela segurança de suas credenciais.</li>
                    <li>A Central3D reserva-se o direito de suspender ou encerrar contas que violem estes termos.</li>
                </ul>
            </section>

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-sm"><i class="fas fa-store"></i></span>
                    3. Uso da Plataforma
                </h2>
                <ul class="text-zinc-600 dark:text-zinc-300 leading-relaxed space-y-2 list-disc list-inside">
                    <li>A plataforma permite que makers criem lojas virtuais para venda de produtos de impressão 3D.</li>
                    <li>O vendedor é inteiramente responsável pelos produtos que anuncia, incluindo descrição, qualidade e entrega.</li>
                    <li>É proibido anunciar produtos ilegais, ofensivos ou que violem direitos de terceiros.</li>
                    <li>A Central3D atua como marketplace e não se responsabiliza por transações realizadas entre vendedores e compradores.</li>
                </ul>
            </section>

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-sm"><i class="fas fa-credit-card"></i></span>
                    4. Planos e Pagamentos
                </h2>
                <ul class="text-zinc-600 dark:text-zinc-300 leading-relaxed space-y-2 list-disc list-inside">
                    <li>A plataforma oferece planos gratuitos e pagos. As funcionalidades de cada plano estão descritas na página de Planos.</li>
                    <li>Os pagamentos são processados por meio de plataformas de pagamento terceirizadas (ex: Stripe).</li>
                    <li>A cobrança é recorrente e pode ser cancelada a qualquer momento pelo usuário.</li>
                </ul>
            </section>

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-sm"><i class="fas fa-copyright"></i></span>
                    5. Propriedade Intelectual
                </h2>
                <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed">
                    Todo o conteúdo da plataforma (marca, layout, código e textos) é de propriedade da Central3D. Os modelos 3D e produtos são de propriedade de seus respectivos criadores/vendedores. É proibida a reprodução, distribuição ou uso comercial sem autorização expressa.
                </p>
            </section>

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-sm"><i class="fas fa-exclamation-triangle"></i></span>
                    6. Limitação de Responsabilidade
                </h2>
                <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed">
                    A Central3D não se responsabiliza por danos diretos, indiretos ou consequenciais decorrentes do uso da plataforma. A disponibilidade dos serviços é oferecida "como está", sem garantias de funcionamento ininterrupto. Faremos nosso melhor para manter a plataforma estável e segura.
                </p>
            </section>

            <section class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-sm"><i class="fas fa-gavel"></i></span>
                    7. Disposições Gerais
                </h2>
                <ul class="text-zinc-600 dark:text-zinc-300 leading-relaxed space-y-2 list-disc list-inside">
                    <li>Estes termos podem ser alterados a qualquer momento, mediante aviso na plataforma.</li>
                    <li>O foro competente para dirimir eventuais controvérsias será o da comarca do responsável legal pelo CNPJ.</li>
                    <li>Ao continuar utilizando a plataforma após alterações, o usuário concorda com os novos termos.</li>
                </ul>
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
