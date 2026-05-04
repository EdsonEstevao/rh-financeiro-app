{{-- resources/views/financeiro/reports/pdfs/credit-cards.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório de Cartões de Crédito</title>
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
            border-bottom: 2px solid #8B5CF6;
            padding-bottom: 10px
        }

        .company-name {
            font-size: 20px;
            font-weight: bold
        }

        .report-title {
            font-size: 16px;
            color: #8B5CF6;
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
            background: #F5F3FF;
            border: 1px solid #DDD6FE;
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
            color: #5B21B6
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
            background: #F5F3FF !important;
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
    </style>
</head>

<body>
    <div class="header">
        <div class="company-name">{{ config('app.name') }}</div>
        <div class="report-title">Relatório de Cartões de Crédito</div>
    </div>
    <div class="filters"><strong>Status:</strong> {{ $filters['status'] }} | <strong>Período:</strong>
        {{ $filters['period'] }} | <strong>Gerado em:</strong> {{ $filters['date'] }}</div>
    <div class="summary-cards">
        <div class="summary-card">
            <div class="label">Total Transações</div>
            <div class="value">{{ $summary['total_transactions'] }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Valor Total</div>
            <div class="value">R$ {{ number_format($summary['total_amount'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Aprovado</div>
            <div class="value">R$ {{ number_format($summary['approved_amount'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Ticket Médio</div>
            <div class="value">R$ {{ number_format($summary['average_ticket'], 2, ',', '.') }}</div>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Transação</th>
                <th>Cliente</th>
                <th>Bandeira</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $t)
                <tr>
                    <td>{{ $t->transaction_id }}</td>
                    <td>{{ $t->customer_name }}</td>
                    <td>{{ strtoupper($t->card_brand) }}</td>
                    <td>R$ {{ number_format($t->amount, 2, ',', '.') }}</td>
                    <td>{{ ucfirst($t->status) }}</td>
                    <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3"><strong>TOTAL ({{ $summary['total_transactions'] }} transações)</strong></td>
                <td><strong>R$ {{ number_format($summary['total_amount'], 2, ',', '.') }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
    <div class="footer">{{ config('app.name') }} - Página {PAGE_NUM} de {PAGE_COUNT}</div>
</body>

</html>
