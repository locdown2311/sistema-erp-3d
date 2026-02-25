@extends('layouts.app')

@section('page-title', 'Nova Venda')

@section('content')
<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden max-w-4xl mb-6">
    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-cart-plus text-emerald-500"></i> Registrar Venda
        </h3>
    </div>

    <form method="POST" action="{{ route('sales.store') }}" id="saleForm" class="p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Cliente</label>
                <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="Nome do cliente (opcional)" 
                       class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Data da Venda <span class="text-red-500">*</span></label>
                <input type="date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}" required 
                       class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
            </div>
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Observações</label>
            <textarea name="notes" rows="2" placeholder="Observações sobre a venda..." 
                      class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none resize-none placeholder-zinc-400 dark:placeholder-zinc-600">{{ old('notes') }}</textarea>
        </div>

        {{-- Items --}}
        <div class="my-8 pt-6 border-t border-zinc-200 dark:border-zinc-800">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Itens da Venda</h4>
                <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-sm" onclick="addItem()">
                    <i class="fas fa-plus"></i> Adicionar Item
                </button>
            </div>

            <div id="saleItems" class="space-y-4">
                <div class="sale-item grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_auto] gap-4 items-end p-4 bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-xl">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Produto <span class="text-red-500">*</span></label>
                        <select name="items[0][product_id]" required onchange="updatePrice(this)"
                                class="product-select w-full px-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                            <option value="">Selecione...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->base_price }}">{{ $product->name }} — R$ {{ number_format($product->base_price, 2, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Qtd <span class="text-red-500">*</span></label>
                        <input type="number" name="items[0][quantity]" class="item-qty w-full px-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none" min="1" value="1" required onchange="calcTotal()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Preço Unit. <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-zinc-500 dark:text-zinc-400 sm:text-xs text-xs mt-0.5">R$</span>
                            </div>
                            <input type="number" name="items[0][unit_price]" class="item-price w-full pl-8 pr-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none" step="0.01" min="0" value="0.00" required onchange="calcTotal()">
                        </div>
                    </div>
                    <button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10 transition-colors" onclick="removeItem(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex flex-col items-end p-5 bg-gradient-to-br from-emerald-50 to-cyan-50 dark:from-emerald-950/30 dark:to-cyan-950/30 border border-emerald-100 dark:border-emerald-900/50 rounded-xl mb-6">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1">Total da Venda</span>
            <div id="saleTotal" class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">R$ 0,00</div>
        </div>

        <div class="flex items-center gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-800">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:focus:ring-offset-zinc-900">
                <i class="fas fa-check"></i> Finalizar Venda
            </button>
            <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-sm">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
let itemIndex = 1;
const products = @json($products);

function addItem() {
    const container = document.getElementById('saleItems');
    const div = document.createElement('div');
    div.className = 'sale-item grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_auto] gap-4 items-end p-4 bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-xl';

    let options = '<option value="">Selecione...</option>';
    products.forEach(p => {
        options += `<option value="${p.id}" data-price="${p.base_price}">${p.name} — R$ ${parseFloat(p.base_price).toFixed(2).replace('.', ',')}</option>`;
    });

    div.innerHTML = `
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Produto *</label>
            <select name="items[${itemIndex}][product_id]" required onchange="updatePrice(this)"
                    class="product-select w-full px-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                ${options}
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Qtd *</label>
            <input type="number" name="items[${itemIndex}][quantity]" min="1" value="1" required onchange="calcTotal()"
                   class="item-qty w-full px-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Preço Unit. *</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-zinc-500 dark:text-zinc-400 sm:text-xs text-xs mt-0.5">R$</span>
                </div>
                <input type="number" name="items[${itemIndex}][unit_price]" step="0.01" min="0" value="0.00" required onchange="calcTotal()"
                       class="item-price w-full pl-8 pr-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
            </div>
        </div>
        <button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10 transition-colors" onclick="removeItem(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
    itemIndex++;
}

function removeItem(btn) {
    const items = document.querySelectorAll('.sale-item');
    if (items.length > 1) {
        btn.closest('.sale-item').remove();
        calcTotal();
    }
}

function updatePrice(select) {
    const option = select.options[select.selectedIndex];
    const price = option.getAttribute('data-price') || 0;
    const row = select.closest('.sale-item');
    row.querySelector('.item-price').value = parseFloat(price).toFixed(2);
    calcTotal();
}

function calcTotal() {
    let total = 0;
    document.querySelectorAll('.sale-item').forEach(item => {
        const qty = parseFloat(item.querySelector('.item-qty')?.value) || 0;
        const price = parseFloat(item.querySelector('.item-price')?.value) || 0;
        total += qty * price;
    });
    document.getElementById('saleTotal').textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
}
</script>
@endsection
