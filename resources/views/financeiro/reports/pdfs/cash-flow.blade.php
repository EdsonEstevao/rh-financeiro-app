{{-- resources/views/financeiro/reports/pdfs/cash-flow.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Fluxo de Caixa</title>
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
            border-bottom: 2px solid #10B981;
            padding-bottom: 10px
        }

        .company-name {
            font-size: 20px;
            font-weight: bold
        }

        .report-title {
            font-size: 16px;
            color: #10B981;
            margin-top: 5px
        }

        .filters {
            margin: 20px 0;
            padding: 10px;
            background: #F3F4F6;
            border-radius: 5px;
            font-size: 11px
        }

        .summary {
            margin: 20px 0;
            padding: 15px;
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            border-radius: 5px;
            text-align: center
        }

        .summary .total {
            font-size: 24px;
            font-weight: bold;
            color: #059669
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

        .amount {
            text-align: right
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
        <div class="report-title">Fluxo de Caixa - {{ $filters['period'] }}</div>
    </div>
    <div class="filters"><strong>Período:</strong> {{ $filters['period'] }} | <strong>Gerado em:</strong>
        {{ $filters['date'] }}</div>
    <div class="summary">
        <div class="total">Receita Total: R$ {{ number_format($total_income ?? 0, 2, ',', '.') }}</div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Tipo</th>
                <th class="amount">Valor</th>
            </tr>
        </thead>
        <tbody>
            @php $runningTotal = 0; @endphp
            @foreach ($incomes ?? [] as $income)
                @php $runningTotal += $income['amount']; @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($income['date'])->format('d/m/Y') }}</td>
                    <td>{{ $income['description'] }}</td>
                    <td>{{ $income['type'] }}</td>
                    <td class="amount">R$ {{ number_format($income['amount'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#ECFDF5;font-weight:bold">
                <td colspan="3" class="amount"><strong>Total Acumulado</strong></td>
                <td class="amount"><strong>R$ {{ number_format($runningTotal, 2, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>
    <div class="footer">{{ config('app.name') }} - Página {PAGE_NUM} de {PAGE_COUNT}</div>
</body>

</html>
