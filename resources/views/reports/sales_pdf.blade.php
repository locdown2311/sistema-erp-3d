<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Vendas — {{ $storeName }}</title>
    <style>
        /* ── Reset & Base ────────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.5;
            background: #fff;
        }

        /* ── Header ──────────────────────────────────────── */
        .header {
            border-bottom: 3px solid #4f46e5;
            padding: 15px 0 12px;
            margin-bottom: 18px;
        }
        .header-inner {
            width: 100%;
        }
        .header-inner td { vertical-align: middle; }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            letter-spacing: -0.3px;
        }
        .report-title {
            font-size: 13px;
            color: #6b7280;
            margin-top: 2px;
        }
        .report-meta {
            text-align: right;
            font-size: 10px;
            color: #9ca3af;
        }
        .report-meta strong {
            color: #4f46e5;
            font-size: 11px;
        }
        .logo-img {
            max-height: 50px;
            max-width: 120px;
        }

        /* ── Filtros ─────────────────────────────────────── */
        .filters-bar {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 16px;
            font-size: 10px;
            color: #6b7280;
        }
        .filters-bar strong { color: #374151; }

        /* ── KPI Cards ───────────────────────────────────── */
        .kpi-table { width: 100%; margin-bottom: 18px; border-spacing: 8px; }
        .kpi-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px 14px;
            text-align: center;
            width: 25%;
        }
        .kpi-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #9ca3af;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .kpi-value {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
        }
        .kpi-sub {
            font-size: 9px;
            color: #6b7280;
            margin-top: 1px;
        }
        .kpi-green .kpi-value { color: #059669; }
        .kpi-amber .kpi-value { color: #d97706; }
        .kpi-red .kpi-value { color: #dc2626; }
        .kpi-indigo .kpi-value { color: #4f46e5; }

        /* ── Status Summary Bar ──────────────────────────── */
        .status-summary {
            width: 100%;
            margin-bottom: 16px;
            border-spacing: 6px;
        }
        .status-box {
            border-radius: 5px;
            padding: 8px 12px;
            text-align: center;
            width: 33.33%;
        }
        .status-box-green { background: #ecfdf5; border: 1px solid #a7f3d0; }
        .status-box-amber { background: #fffbeb; border: 1px solid #fde68a; }
        .status-box-red { background: #fef2f2; border: 1px solid #fecaca; }
        .status-box .s-count { font-size: 16px; font-weight: bold; }
        .status-box .s-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.4px; }
        .status-box-green .s-count { color: #059669; }
        .status-box-green .s-label { color: #065f46; }
        .status-box-amber .s-count { color: #d97706; }
        .status-box-amber .s-label { color: #92400e; }
        .status-box-red .s-count { color: #dc2626; }
        .status-box-red .s-label { color: #991b1b; }

        /* ── Section Headers ─────────────────────────────── */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 2px solid #e5e7eb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-icon { color: #4f46e5; margin-right: 4px; }

        /* ── Tables ──────────────────────────────────────── */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 10px;
            text-align: left;
        }
        table.data-table th {
            background: #f3f4f6;
            color: #6b7280;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        table.data-table tr:nth-child(even) td { background-color: #f9fafb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .status-completed { color: #059669; font-weight: bold; }
        .status-pending { color: #d97706; font-weight: bold; }
        .status-cancelled { color: #dc2626; font-weight: bold; }

        /* ── Total Row ───────────────────────────────────── */
        .total-row td {
            background: #4f46e5 !important;
            color: #fff !important;
            font-weight: bold;
            font-size: 12px;
            border-color: #4338ca !important;
        }

        /* ── Top Products ────────────────────────────────── */
        table.ranking-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 10px;
        }
        table.ranking-table th, table.ranking-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 10px;
        }
        table.ranking-table th {
            background: #eef2ff;
            color: #4338ca;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: bold;
        }
        table.ranking-table tr:nth-child(even) td { background: #f5f3ff; }
        .rank-badge {
            display: inline-block;
            background: #4f46e5;
            color: #fff;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            text-align: center;
            line-height: 18px;
            font-size: 9px;
            font-weight: bold;
        }
        .bar-bg {
            background: #e5e7eb;
            border-radius: 3px;
            height: 8px;
            width: 100%;
        }
        .bar-fill {
            background: #4f46e5;
            border-radius: 3px;
            height: 8px;
        }

        /* ── Footer ──────────────────────────────────────── */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }

        /* ── Page Break Helpers ───────────────────────────── */
        .page-break { page-break-before: always; }
        .no-break { page-break-inside: avoid; }
    </style>
</head>
<body>

    {{-- ════════ HEADER ════════ --}}
    <div class="header">
        <table class="header-inner" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 70%;">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" class="logo-img" alt="Logo"><br>
                    @endif
                    <span class="company-name">{{ $storeName }}</span>
                    <div class="report-title">Relatório Consolidado de Vendas</div>
                </td>
                <td class="report-meta">
                    <strong>Gerado em</strong><br>
                    {{ now()->format('d/m/Y') }}<br>
                    {{ now()->format('H:i:s') }}
                </td>
            </tr>
        </table>
    </div>

    {{-- ════════ FILTROS ATIVOS ════════ --}}
    <div class="filters-bar">
        <strong>Período:</strong>
        @if(isset($filters['date_from']) || isset($filters['date_to']))
            {{ isset($filters['date_from']) ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'Início' }}
            até
            {{ isset($filters['date_to']) ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'Hoje' }}
        @else
            Todo o período
        @endif
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Status:</strong>
        @if(isset($filters['status']) && $filters['status'])
            {{ $filters['status'] === 'completed' ? 'Concluída' : ($filters['status'] === 'pending' ? 'Pendente' : 'Cancelada') }}
        @else
            Todos
        @endif
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Total de registros:</strong> {{ $sales->count() }}
    </div>

    {{-- ════════ KPI CARDS ════════ --}}
    <table class="kpi-table" cellpadding="0" cellspacing="8">
        <tr>
            <td class="kpi-card kpi-indigo">
                <div class="kpi-label">Receita Total</div>
                <div class="kpi-value">R$ {{ number_format($totalSum, 2, ',', '.') }}</div>
                <div class="kpi-sub">{{ $sales->count() }} venda(s)</div>
            </td>
            <td class="kpi-card kpi-green">
                <div class="kpi-label">Concluídas</div>
                <div class="kpi-value">R$ {{ number_format($totalCompleted, 2, ',', '.') }}</div>
                <div class="kpi-sub">{{ $countCompleted }} venda(s)</div>
            </td>
            <td class="kpi-card">
                <div class="kpi-label">Ticket Médio</div>
                <div class="kpi-value">R$ {{ number_format($avgTicket, 2, ',', '.') }}</div>
                <div class="kpi-sub">por venda</div>
            </td>
            <td class="kpi-card">
                <div class="kpi-label">Itens Vendidos</div>
                <div class="kpi-value">{{ number_format($totalItems, 0, ',', '.') }}</div>
                <div class="kpi-sub">unidades</div>
            </td>
        </tr>
    </table>

    {{-- ════════ STATUS BREAKDOWN ════════ --}}
    <table class="status-summary" cellpadding="0" cellspacing="6">
        <tr>
            <td class="status-box status-box-green">
                <div class="s-count">{{ $countCompleted }}</div>
                <div class="s-label">Concluídas — R$ {{ number_format($totalCompleted, 2, ',', '.') }}</div>
            </td>
            <td class="status-box status-box-amber">
                <div class="s-count">{{ $countPending }}</div>
                <div class="s-label">Pendentes — R$ {{ number_format($totalPending, 2, ',', '.') }}</div>
            </td>
            <td class="status-box status-box-red">
                <div class="s-count">{{ $countCancelled }}</div>
                <div class="s-label">Canceladas — R$ {{ number_format($totalCancelled, 2, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    {{-- ════════ TOP 5 PRODUTOS ════════ --}}
    @if($productRanking->count() > 0)
    <div class="no-break">
        <div class="section-title">Top 5 Produtos Mais Vendidos</div>
        <table class="ranking-table">
            <thead>
                <tr>
                    <th style="width:6%; text-align:center;">#</th>
                    <th style="width:44%;">Produto</th>
                    <th style="width:12%; text-align:center;">Qtd</th>
                    <th style="width:20%; text-align:right;">Faturamento</th>
                    <th style="width:18%;">Proporção</th>
                </tr>
            </thead>
            <tbody>
                @php $topMax = $productRanking->first()['total']; $rank = 1; @endphp
                @foreach($productRanking as $prodName => $data)
                    <tr>
                        <td class="text-center"><span class="rank-badge">{{ $rank }}</span></td>
                        <td class="font-bold">{{ $prodName }}</td>
                        <td class="text-center">{{ number_format($data['qty'], 0, ',', '.') }}</td>
                        <td class="text-right font-bold">R$ {{ number_format($data['total'], 2, ',', '.') }}</td>
                        <td>
                            <div class="bar-bg">
                                <div class="bar-fill" style="width: {{ $topMax > 0 ? round(($data['total'] / $topMax) * 100) : 0 }}%;"></div>
                            </div>
                        </td>
                    </tr>
                    @php $rank++; @endphp
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ════════ TABELA DETALHADA DE VENDAS ════════ --}}
    <div class="section-title">Detalhamento de Vendas</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:6%;">#</th>
                <th style="width:26%;">Cliente</th>
                <th style="width:12%; text-align:center;">Data</th>
                <th style="width:8%; text-align:center;">Itens</th>
                <th style="width:14%; text-align:center;">Rastreio</th>
                <th style="width:12%; text-align:center;">Status</th>
                <th style="width:16%; text-align:right;">Total (R$)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->customer_name ?? '—' }}</td>
                    <td class="text-center">{{ $sale->sale_date->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $sale->items->sum('quantity') }}</td>
                    <td class="text-center" style="font-size:9px;">
                        @if($sale->tracking_code)
                            {{ $sale->tracking_code }}
                            @if($sale->shipping_status)
                                <br><span style="color:#6b7280;">({{ $sale->shipping_status }})</span>
                            @endif
                        @else
                            <span style="color:#d1d5db;">—</span>
                        @endif
                    </td>
                    <td class="text-center {{ $sale->status === 'completed' ? 'status-completed' : ($sale->status === 'pending' ? 'status-pending' : 'status-cancelled') }}">
                        {{ $sale->status === 'completed' ? 'Concluída' : ($sale->status === 'pending' ? 'Pendente' : 'Cancelada') }}
                    </td>
                    <td class="text-right font-bold">{{ number_format($sale->total, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color:#9ca3af;">Nenhuma venda encontrada para os filtros selecionados.</td>
                </tr>
            @endforelse
        </tbody>
        @if($sales->count() > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL GERAL:</td>
                <td class="text-right">R$ {{ number_format($totalSum, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- ════════ FOOTER ════════ --}}
    <div class="footer">
        <strong>{{ $storeName }}</strong> — Relatório gerado automaticamente em {{ now()->format('d/m/Y \à\s H:i') }}<br>
        Este documento é gerado eletronicamente e não possui validade fiscal.
    </div>

</body>
</html>
