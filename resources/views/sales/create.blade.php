@extends('layouts.app')

@section('page-title', 'Nova Venda')

@section('content')
<div class="card" style="max-width: 900px;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-cart-plus" style="margin-right: 8px; color: var(--success);"></i>Registrar Venda</h3>
    </div>

    <form method="POST" action="{{ route('sales.store') }}" id="saleForm">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Cliente</label>
                <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" placeholder="Nome do cliente (opcional)">
            </div>
            <div class="form-group">
                <label class="form-label">Data da Venda *</label>
                <input type="date" name="sale_date" class="form-control" value="{{ old('sale_date', date('Y-m-d')) }}" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Observações</label>
            <textarea name="notes" class="form-control" style="min-height: 60px;" placeholder="Observações sobre a venda...">{{ old('notes') }}</textarea>
        </div>

        {{-- Items --}}
        <div style="margin: var(--space-lg) 0;">
            <div class="flex-between" style="margin-bottom: var(--space-md);">
                <h4 style="font-size: 1rem; font-weight: 600;">Itens da Venda</h4>
                <button type="button" class="btn btn-outline btn-sm" onclick="addItem()"><i class="fas fa-plus"></i> Adicionar Item</button>
            </div>

            <div id="saleItems">
                <div class="sale-item" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: var(--space-sm); align-items: end; margin-bottom: var(--space-md); padding: var(--space-md); background: var(--bg-input); border-radius: var(--radius-md);">
                    <div>
                        <label class="form-label">Produto *</label>
                        <select name="items[0][product_id]" class="form-control product-select" required onchange="updatePrice(this)">
                            <option value="">Selecione...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->base_price }}">{{ $product->name }} — R$ {{ number_format($product->base_price, 2, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Qtd *</label>
                        <input type="number" name="items[0][quantity]" class="form-control item-qty" min="1" value="1" required onchange="calcTotal()">
                    </div>
                    <div>
                        <label class="form-label">Preço Unit. *</label>
                        <input type="number" name="items[0][unit_price]" class="form-control item-price" step="0.01" min="0" value="0.00" required onchange="calcTotal()">
                    </div>
                    <button type="button" class="btn btn-danger btn-icon" onclick="removeItem(this)" style="margin-bottom: 2px;"><i class="fas fa-times"></i></button>
                </div>
            </div>
        </div>

        <div style="text-align: right; padding: var(--space-md); background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(6, 182, 212, 0.05)); border-radius: var(--radius-md); margin-bottom: var(--space-lg);">
            <span style="font-size: 0.85rem; color: var(--text-secondary);">Total da Venda</span>
            <div id="saleTotal" style="font-size: 1.5rem; font-weight: 700; color: var(--success-light);">R$ 0,00</div>
        </div>

        <div style="display: flex; gap: var(--space-sm);">
            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Finalizar Venda</button>
            <a href="{{ route('sales.index') }}" class="btn btn-outline">Cancelar</a>
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
    div.className = 'sale-item';
    div.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: var(--space-sm); align-items: end; margin-bottom: var(--space-md); padding: var(--space-md); background: var(--bg-input); border-radius: var(--radius-md);';

    let options = '<option value="">Selecione...</option>';
    products.forEach(p => {
        options += `<option value="${p.id}" data-price="${p.base_price}">${p.name} — R$ ${parseFloat(p.base_price).toFixed(2).replace('.', ',')}</option>`;
    });

    div.innerHTML = `
        <div>
            <label class="form-label">Produto *</label>
            <select name="items[${itemIndex}][product_id]" class="form-control product-select" required onchange="updatePrice(this)">
                ${options}
            </select>
        </div>
        <div>
            <label class="form-label">Qtd *</label>
            <input type="number" name="items[${itemIndex}][quantity]" class="form-control item-qty" min="1" value="1" required onchange="calcTotal()">
        </div>
        <div>
            <label class="form-label">Preço Unit. *</label>
            <input type="number" name="items[${itemIndex}][unit_price]" class="form-control item-price" step="0.01" min="0" value="0.00" required onchange="calcTotal()">
        </div>
        <button type="button" class="btn btn-danger btn-icon" onclick="removeItem(this)" style="margin-bottom: 2px;"><i class="fas fa-times"></i></button>
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
