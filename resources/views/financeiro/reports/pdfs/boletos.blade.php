{{-- resources/views/financeiro/reports/pdfs/boletos.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório de Boletos</title>
    <style>
        @page {
            margin: 100px 25px 50px
        }

        body {
            font-family: Helvetica, sans-serif;
            font-size: 12px;
            color: #333
        }

        .header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 10px
        }

        .company-name {
            font-size: 20px;
            font-weight: bold
        }

        .report-title {
            font-size: 16px;
            color: #3B82F6;
            margin-top: 5px
        }

        .filters {
            margin: 20px 0;
            padding: 10px;
            background: #F3F4F6;
            border-radius: 5px;
            font-size: 11px
        }

        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin: 20px 0
        }

        .summary-card {
            flex: 1;
            padding: 10px;
            margin: 0 5px;
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 5px;
            text-align: center
        }

        .summary-card .label {
            font-size: 10px;
            color: #6B7280;
            text-transform: uppercase
        }

        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #1F2937
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px
        }

        thead {
            background: #1F2937;
            color: white
        }

        th {
            padding: 8px 10px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 10px
        }

        td {
            padding: 8px 10px;
            border-bottom: 1px solid #E5E7EB
        }

        tr:nth-child(even) {
            background: #F9FAFB
        }

        .total-row {
            background: #EFF6FF !important;
            font-weight: bold;
            font-size: 12px
        }

        .footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            text-align: center;
            font-size: 10px;
            color: #666
        }

        .status-paid {
            color: #059669
        }

        .status-pending {
            color: #D97706
        }

        .status-overdue {
            color: #DC2626
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-name">{{ config('app.name') }}</div>
        <div class="report-title">Relatório de Boletos</div>
    </div>
    <div class="filters"><strong>Status:</strong> {{ $filters['status'] }} | <strong>Período:</strong>
        {{ $filters['period'] }} | <strong>Gerado em:</strong> {{ $filters['date'] }}</div>
    <div class="summary-cards">
        <div class="summary-card">
            <div class="label">Total</div>
            <div class="value">R$ {{ number_format($summary['total_amount'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Pagos</div>
            <div class="value">R$ {{ number_format($summary['paid_amount'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Pendentes</div>
            <div class="value">R$ {{ number_format($summary['pending_amount'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Boletos</div>
            <div class="value">{{ $summary['total_count'] }}</div>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Boleto</th>
                <th>Cliente</th>
                <th>Valor</th>
                <th>Vencimento</th>
                <th>Status</th>
                <th>Pagamento</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($boletos as $b)
                <tr>
                    <td>#{{ $b->boleto_number }}</td>
                    <td>{{ $b->user->name ?? $b->payer_name }}</td>
                    <td>R$ {{ number_format($b->amount, 2, ',', '.') }}</td>
                    <td>{{ $b->due_date->format('d/m/Y') }}</td>
                    <td class="status-{{ $b->status }}">{{ ucfirst($b->status) }}</td>
                    <td>{{ $b->paid_at?->format('d/m/Y') ?? '---' }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2"><strong>TOTAL ({{ $summary['total_count'] }} boletos)</strong></td>
                <td><strong>R$ {{ number_format($summary['total_amount'], 2, ',', '.') }}</strong></td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>
    <div class="footer">{{ config('app.name') }} - Página {PAGE_NUM} de {PAGE_COUNT}</div>
</body>

</html>
