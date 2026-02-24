@extends('layouts.app')

@section('page-title', 'Ofertas')

@section('content')
<div class="page-header">
    <h2 class="page-title"><i class="fas fa-tags" style="color: #ff6b35;"></i> Ofertas</h2>
    <a href="{{ route('offers.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nova Oferta</a>
</div>

@if($offers->count() > 0)
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Imagem</th>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Desconto</th>
                    <th>Categoria</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($offers as $offer)
                <tr>
                    <td>
                        @if($offer->image_path)
                            <img src="{{ asset('storage/' . $offer->image_path) }}" alt="" style="width:48px; height:48px; border-radius:var(--radius-md); object-fit:cover;">
                        @else
                            <div style="width:48px; height:48px; border-radius:var(--radius-md); background:var(--bg-input); display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-image" style="color:var(--text-muted);"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $offer->name }}</strong>
                        @if($offer->description)
                            <br><span style="font-size:0.78rem; color:var(--text-muted);">{{ Str::limit($offer->description, 60) }}</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-weight:600; color:var(--success-light);">R$ {{ number_format($offer->price, 2, ',', '.') }}</span>
                        @if($offer->original_price)
                            <br><span style="font-size:0.75rem; color:var(--text-muted); text-decoration:line-through;">R$ {{ number_format($offer->original_price, 2, ',', '.') }}</span>
                        @endif
                    </td>
                    <td>
                        @if($offer->discount_percent)
                            <span class="badge" style="background:linear-gradient(135deg, #ff6b35, #ff4444); color:white; padding:3px 8px; border-radius:20px; font-size:0.75rem; font-weight:700;">
                                -{{ $offer->discount_percent }}%
                            </span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td><span style="font-size:0.82rem;">{{ $offer->category }}</span></td>
                    <td>
                        @if($offer->active)
                            <span class="badge" style="background:rgba(16,185,129,0.15); color:var(--success-light); padding:3px 10px; border-radius:20px; font-size:0.75rem;">Ativa</span>
                        @else
                            <span class="badge" style="background:rgba(239,68,68,0.15); color:var(--danger-light); padding:3px 10px; border-radius:20px; font-size:0.75rem;">Inativa</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            <a href="{{ $offer->affiliate_url }}" target="_blank" class="btn btn-sm btn-outline" title="Ver link"><i class="fas fa-external-link-alt"></i></a>
                            <a href="{{ route('offers.edit', $offer) }}" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('offers.destroy', $offer) }}" onsubmit="return confirm('Remover oferta?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="empty-state">
        <i class="fas fa-tags"></i>
        <h3>Nenhuma oferta cadastrada</h3>
        <p>Adicione ofertas com links de afiliado para exibir em todas as lojas.</p>
        <a href="{{ route('offers.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Criar Primeira Oferta</a>
    </div>
@endif
@endsection
