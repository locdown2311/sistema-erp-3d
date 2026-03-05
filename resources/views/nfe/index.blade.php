@extends('layouts.app')

@section('title', 'Emissão de NF-e')
@section('page-title', 'Notas Fiscais Eletrônicas')

@section('content')
<div class="max-w-5xl mx-auto mb-8">
    
    <!-- Aviso de Ambiente e Info -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 md:p-5 mb-6 shadow-sm flex items-start gap-4">
        <div class="bg-blue-100 dark:bg-blue-800/50 text-blue-600 dark:text-blue-400 p-2 md:p-3 rounded-lg mt-0.5">
            <i class="fas fa-info-circle text-lg md:text-xl"></i>
        </div>
        <div>
            <h3 class="text-blue-800 dark:text-blue-300 font-semibold mb-1">Protótipo de Emissão de NF-e</h3>
            <p class="text-sm text-blue-700 dark:text-blue-400 leading-relaxed max-w-3xl">
                Preencha os dados abaixo para gerar um XML estrutural de teste de uma Nota Fiscal Eletrônica (Layout 4.00). 
                A NF-e gerada <strong class="font-medium underline decoration-blue-300 dark:decoration-blue-600 underline-offset-2">não será</strong> transmitida para a SEFAZ de forma oficial neste protótipo.
            </p>
        </div>
    </div>

    <form action="{{ route('nfe.emit') }}" method="POST" target="_blank" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Emitente Card -->
            <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50">
                    <h3 class="font-semibold text-zinc-800 dark:text-zinc-200 flex items-center gap-2">
                        <i class="fas fa-store text-indigo-500"></i> Dados do Emitente
                    </h3>
                </div>
                
                <div class="p-5 space-y-5">
                    
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 text-xs px-3 py-2 rounded-md mb-4 flex items-center gap-2">
                        <i class="fas fa-lock"></i> Dados importados automaticamente das Configurações
                    </div>

                    {{-- Logo do Emitente --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1.5">Logo da Empresa</label>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mb-2">Utilizada no DANFE impresso. Carregada das Configurações ou envie uma diretamente.</p>
                        
                        <div class="flex items-center gap-4">
                            <div id="nfe-logo-preview" class="w-20 h-20 rounded-lg border-2 border-dashed border-zinc-300 dark:border-zinc-700 flex items-center justify-center overflow-hidden bg-white dark:bg-zinc-950 shrink-0">
                                @if(auth()->user()->store_logo_url)
                                    <img src="{{ auth()->user()->store_logo_url }}" alt="Logo" class="w-full h-full object-contain" id="nfe-logo-img">
                                @else
                                    <div id="nfe-logo-placeholder" class="text-center">
                                        <i class="fas fa-image text-zinc-300 dark:text-zinc-600 text-2xl"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="cursor-pointer inline-flex items-center gap-2 px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-300 dark:border-zinc-700 rounded-lg text-xs font-medium text-zinc-600 dark:text-zinc-300 transition-colors">
                                    <i class="fas fa-upload text-[10px]"></i> Enviar outra logo
                                    <input type="file" name="nfe_logo" accept="image/png,image/jpeg" class="hidden" id="nfe-logo-input">
                                </label>
                                <span class="text-[10px] text-zinc-400 dark:text-zinc-500">PNG ou JPEG, recomendado 300×100px</span>
                            </div>
                        </div>
                        <input type="hidden" name="nfe_logo_base64" id="nfe-logo-base64" value="">
                    </div>

                    <div class="cursor-not-allowed opacity-80" title="Altere estes dados nas Configurações Gerais.">
                    <div>
                        <label class="block text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1.5">Razão Social</label>
                        <input type="text" name="emit_nome" value="{{ $emit_nome }}" class="w-full px-4 py-2 bg-zinc-100 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm text-zinc-500 dark:text-zinc-500 pointer-events-none" readonly>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1.5">CNPJ</label>
                            <input type="text" name="emit_cnpj" value="{{ $emit_cnpj }}" class="w-full px-4 py-2 bg-zinc-100 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm text-zinc-500 dark:text-zinc-500 pointer-events-none" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1.5">Inscrição Estadual</label>
                            <input type="text" name="emit_ie" value="{{ $emit_ie }}" class="w-full px-4 py-2 bg-zinc-100 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm text-zinc-500 dark:text-zinc-500 pointer-events-none" readonly>
                        </div>
                    </div>
                    </div> {{-- end cursor-not-allowed --}}
                </div>
            </div>

            <!-- Destinatário Card -->
            <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50 flex justify-between items-center">
                    <h3 class="font-semibold text-zinc-800 dark:text-zinc-200 flex items-center gap-2">
                        <i class="fas fa-user-tag text-emerald-500"></i> Dados do Destinatário
                    </h3>
                    <span id="cep-loading" class="text-xs text-emerald-600 dark:text-emerald-400 font-medium hidden flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 rounded-md">
                        <i class="fas fa-spinner fa-spin"></i> Buscando CEP...
                    </span>
                </div>
                
                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2 hidden">
                             <!-- Optional Client Search combo goes here down the road -->
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nome / Razão Social <span class="text-red-500">*</span></label>
                            <input type="text" name="dest_nome" value="CLIENTE TESTE NF-E" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none" required>
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">CPF / CNPJ <span class="text-red-500">*</span></label>
                            <input type="text" name="dest_cpf" value="12345678901" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none tracking-wide" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Inscrição Estadual (IE) do Destinatário</label>
                            <p class="text-xs text-zinc-400 dark:text-zinc-500 mb-1.5">Preencha apenas se o destinatário for <strong>contribuinte do ICMS</strong> (empresa com IE). Deixe em branco para pessoa física ou empresa sem IE.</p>
                            <input type="text" name="dest_ie" value="" placeholder="Ex: 123456789012 (opcional)"
                                class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none tracking-wide font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">CEP <span class="text-red-500">*</span></label>
                            <input type="text" id="dest_cep" name="dest_cep" value="" placeholder="00000-000" maxlength="9" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none tracking-wider font-mono" required>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Logradouro <span class="text-red-500">*</span></label>
                            <input type="text" id="dest_logradouro" name="dest_logradouro" value="" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Número <span class="text-red-500">*</span></label>
                            <input type="text" id="dest_numero" name="dest_numero" value="" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none font-mono" required>
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Bairro <span class="text-red-500">*</span></label>
                            <input type="text" id="dest_bairro" name="dest_bairro" value="" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none" required>
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Município <span class="text-red-500">*</span></label>
                            <input type="text" id="dest_municipio" name="dest_municipio" value="" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none" required>
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">UF <span class="text-red-500">*</span></label>
                            <input type="text" id="dest_uf" name="dest_uf" value="" maxlength="2" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none uppercase font-mono text-center" required>
                        </div>
                    </div>
                </div>
            </div>
            
        </div> <!-- End Grid 2 cols -->

        <!-- Produtos Card Container -->
        <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="font-semibold text-zinc-800 dark:text-zinc-200 flex items-center gap-2">
                    <i class="fas fa-boxes text-orange-500"></i> Itens da Nota
                </h3>
                
                <button type="button" id="btn-add-produto" class="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg transition-colors border border-zinc-300 dark:border-zinc-700 shadow-sm flex items-center justify-center gap-2">
                    <i class="fas fa-plus text-xs"></i> <span>Adicionar Produto</span>
                </button>
            </div>
            
            <div id="produtos-wrapper" class="p-5 space-y-5">
                <!-- Modelo de Produto Repetível -->
                <div class="produto-item bg-zinc-50/50 dark:bg-zinc-800/20 p-5 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 relative group transition-all duration-200 hover:border-zinc-400 dark:hover:border-zinc-500">
                    
                    <button type="button" class="btn-remove-produto absolute -top-3 -right-3 bg-white dark:bg-zinc-800 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 border border-zinc-200 dark:border-zinc-700 rounded-full w-8 h-8 flex items-center justify-center shadow-sm opacity-0 group-hover:opacity-100 transition-all duration-200 hidden z-10" title="Remover Produto">
                        <i class="fas fa-trash-alt text-sm"></i>
                    </button>

                    @if(isset($products) && $products->count() > 0)
                    <div class="mb-5 flex flex-col sm:flex-row gap-2 sm:items-center bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-2.5 rounded-lg shadow-sm">
                        <div class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-500 font-medium px-2 whitespace-nowrap">
                            <i class="fas fa-wand-magic-sparkles"></i> Auto-preencher:
                        </div>
                        <select class="quick_product_select w-full rounded-md border-0 bg-transparent text-sm text-zinc-700 dark:text-zinc-300 focus:ring-0 cursor-pointer outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                            <option value="" class="bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">-- Selecione um produto do seu catálogo --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-nome="{{ $product->name }}" data-valor="{{ number_format($product->base_price, 2, '.', '') }}" class="bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                                    {{ $product->name }} — R$ {{ number_format($product->base_price, 2, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-5 mb-5">
                        <div class="md:col-span-6">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Descrição do Produto <span class="text-red-500">*</span></label>
                            <input type="text" name="prod_descricao[]" value="Peça em PLA - Impressão 3D" class="prod-desc w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none" required>
                        </div>
                        
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Cód. NCM <span class="text-red-500">*</span></label>
                            <select name="prod_ncm[]" class="prod-ncm w-full px-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat" required>
                                <optgroup label="Impressão 3D (Plásticos)">
                                    <option value="39269090" selected>3926.90.90 - Outras Obras (Geral)</option>
                                    <option value="39264000">3926.40.00 - Estatuetas/Ornamentação</option>
                                </optgroup>
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">CFOP <span class="text-red-500">*</span></label>
                            <select name="prod_cfop[]" class="prod-cfop w-full px-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat" required>
                                <optgroup label="Estadual">
                                    <option value="5101" selected>5101 - Venda Própria</option>
                                    <option value="5102">5102 - Revenda</option>
                                </optgroup>
                                <optgroup label="Interestadual">
                                    <option value="6101">6101 - Venda Própria</option>
                                    <option value="6102">6102 - Revenda</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Quantidade <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="prod_qtd[]" value="1.00" class="prod-qtd w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none text-right font-mono" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Valor Unitário (R$) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-sm">R$</span>
                                <input type="number" step="0.01" min="0.01" name="prod_vlr_unit[]" value="100.00" class="prod-vlr-unit w-full pl-9 pr-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none text-right font-mono" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Valor Total Bruto (R$)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-emerald-600 dark:text-emerald-500 text-sm font-medium">R$</span>
                                <input type="number" step="0.01" name="prod_vlr_total[]" value="100.00" class="prod-vlr-total bg-emerald-50/50 dark:bg-emerald-900/10 w-full pl-9 pr-4 py-2 border border-emerald-200 dark:border-emerald-800 rounded-lg text-sm text-emerald-800 dark:text-emerald-200 font-semibold focus:ring-0 outline-none text-right font-mono" readonly required tabindex="-1">
                            </div>
                        </div>
                    </div>
                </div> <!-- End Produto Item -->
            </div>
            
            <div class="p-5 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                    O cálculo de ICMS integrado ao Simples Nacional será realizado no backend.
                </div>
                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2 focus:ring-4 focus:ring-emerald-500/30 outline-none">
                    <i class="fas fa-file-code text-lg"></i>
                    Gerar XML e Autorizar (Teste)
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ── Logo preview ──────────────────────────────────────
        const logoInput = document.getElementById('nfe-logo-input');
        if (logoInput) {
            logoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function(ev) {
                    const previewBox = document.getElementById('nfe-logo-preview');
                    previewBox.innerHTML = '<img src="' + ev.target.result + '" class="w-full h-full object-contain" id="nfe-logo-img">';
                    document.getElementById('nfe-logo-base64').value = ev.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        const container = document.getElementById('produtos-wrapper');
        const btnAdd = document.getElementById('btn-add-produto');
        
        // Função para recalcular o total de uma linha específica
        function recalcularTotalItem(itemElement) {
            const qtdInput = itemElement.querySelector('.prod-qtd');
            const vlrUnitInput = itemElement.querySelector('.prod-vlr-unit');
            const vlrTotalInput = itemElement.querySelector('.prod-vlr-total');
            
            const qtd = parseFloat(qtdInput.value) || 0;
            const val = parseFloat(vlrUnitInput.value) || 0;
            vlrTotalInput.value = (qtd * val).toFixed(2);
        }

        // Delegação de eventos para suportar clones dinâmicos
        container.addEventListener('change', function(e) {
            // Mudança no select de Preenchimento Rapido
            if (e.target.classList.contains('quick_product_select')) {
                const selected = e.target.options[e.target.selectedIndex];
                const itemContainer = e.target.closest('.produto-item');
                
                if (selected.value) {
                    const nome = selected.getAttribute('data-nome');
                    const valor = selected.getAttribute('data-valor');
                    
                    itemContainer.querySelector('.prod-desc').value = nome;
                    itemContainer.querySelector('.prod-vlr-unit').value = valor;
                    
                    recalcularTotalItem(itemContainer);
                    
                    // Reset select para permitir escolher o mesmo caso precise
                    e.target.value = "";
                }
            }
        });

        container.addEventListener('input', function(e) {
            // Recálculo ao mudar quantidade ou valor unitário digitando
            if (e.target.classList.contains('prod-qtd') || e.target.classList.contains('prod-vlr-unit')) {
                const itemContainer = e.target.closest('.produto-item');
                recalcularTotalItem(itemContainer);
            }
        });

        container.addEventListener('click', function(e) {
            // Remover produto
            const btnRemove = e.target.closest('.btn-remove-produto');
            if (btnRemove) {
                const itemsCount = container.querySelectorAll('.produto-item').length;
                if (itemsCount > 1) {
                    btnRemove.closest('.produto-item').remove();
                    atualizarBotoesRemover();
                } else {
                    alert('A nota fiscal deve ter pelo menos um produto.');
                }
            }
        });

        // Adicionar novo Produto
        btnAdd.addEventListener('click', function() {
            const firstItem = container.querySelector('.produto-item');
            const clone = firstItem.cloneNode(true);
            
            // Limpa os valores do clone
            clone.querySelector('.quick_product_select').value = "";
            clone.querySelector('.prod-desc').value = "";
            clone.querySelector('.prod-qtd').value = "1";
            clone.querySelector('.prod-vlr-unit').value = "0.00";
            clone.querySelector('.prod-vlr-total').value = "0.00";
            
            // Retorna Selects fixos ao valor padrão
            clone.querySelector('.prod-ncm').value = "39269090";
            clone.querySelector('.prod-cfop').value = "5101";
            
            container.appendChild(clone);
            atualizarBotoesRemover();
        });

        // Mostra/oculta botão de remover dependendo de quantos itens existem
        function atualizarBotoesRemover() {
            const items = container.querySelectorAll('.produto-item');
            const btnsRemove = container.querySelectorAll('.btn-remove-produto');
            
            if (items.length > 1) {
                btnsRemove.forEach(btn => btn.classList.remove('hidden'));
            } else {
                btnsRemove.forEach(btn => btn.classList.add('hidden'));
            }
        }

        // --- VIA CEP Logic ---
        const cepInput = document.getElementById('dest_cep');
        const loadingCep = document.getElementById('cep-loading');
        
        if (cepInput) {
            // Formata CEP enquanto digita
            cepInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 5) {
                    value = value.substring(0, 5) + '-' + value.substring(5, 8);
                }
                e.target.value = value;
            });

            // Busca BrasilAPI no Blur ou Enter
            const searchCep = async () => {
                const cep = cepInput.value.replace(/\D/g, '');
                
                if (cep.length === 8) {
                    if(loadingCep) loadingCep.classList.remove('hidden');
                    cepInput.classList.add('opacity-50');
                    
                    try {
                        const response = await fetch(`https://brasilapi.com.br/api/cep/v2/${cep}`);
                        
                        if (response.ok) {
                            const data = await response.json();
                            document.getElementById('dest_logradouro').value = data.street || '';
                            document.getElementById('dest_bairro').value = data.neighborhood || '';
                            document.getElementById('dest_municipio').value = data.city || '';
                            document.getElementById('dest_uf').value = data.state || '';
                            // Foco no número para o usuário finalizar
                            document.getElementById('dest_numero').focus();
                        } else {
                            alert('CEP não encontrado na BrasilAPI.');
                        }
                    } catch (error) {
                        console.error('Erro ao buscar o CEP:', error);
                        alert('Erro ao comunicar com o servidor de CEP.');
                    } finally {
                        if(loadingCep) loadingCep.classList.add('hidden');
                        cepInput.classList.remove('opacity-50');
                    }
                }
            };

            cepInput.addEventListener('blur', searchCep);
            
            cepInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchCep();
                }
            });
        }
    });
</script>
@endsection
