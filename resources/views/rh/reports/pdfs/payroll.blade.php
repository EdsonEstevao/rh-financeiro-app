{{-- resources/views/rh/reports/pdfs/payroll.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório de Folha de Pagamento - {{ $filters['period'] }}</title>
    <style>
        @page {
            margin: 100px 25px 50px 25px;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 60px;
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 10px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #1F2937;
        }

        .report-title {
            font-size: 16px;
            color: #3B82F6;
            margin-top: 5px;
        }

        .filters {
            margin: 20px 0;
            padding: 10px;
            background: #F3F4F6;
            border-radius: 5px;
            font-size: 11px;
        }

        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }

        .summary-card {
            flex: 1;
            padding: 10px;
            margin: 0 5px;
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 5px;
            text-align: center;
        }

        .summary-card .label {
            font-size: 10px;
            color: #6B7280;
            text-transform: uppercase;
        }

        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #1F2937;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px;
        }

        thead {
            background: #1F2937;
            color: white;
        }

        th {
            padding: 8px 10px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 10px;
        }

        td {
            padding: 8px 10px;
            border-bottom: 1px solid #E5E7EB;
        }

        tr:nth-child(even) {
            background: #F9FAFB;
        }

        .total-row {
            background: #EFF6FF !important;
            font-weight: bold;
            font-size: 12px;
        }

        .footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            height: 20px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .amount {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-name">{{ config('app.name') }}</div>
        <div class="report-title">Relatório de Folha de Pagamento</div>
    </div>
    <div class="filters">
        <strong>Período:</strong> {{ $filters['period'] }} |
        <strong>Departamento:</strong> {{ $filters['department'] }} |
        <strong>Gerado em:</strong> {{ now()->format('d/m/Y H:i') }}
    </div>
    <div class="summary-cards">
        <div class="summary-card">
            <div class="label">Salários Base</div>
            <div class="value">R$ {{ number_format($summary['total_base_salary'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Descontos</div>
            <div class="value">R$ {{ number_format($summary['total_deductions'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Bônus</div>
            <div class="value">R$ {{ number_format($summary['total_bonuses'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Líquido</div>
            <div class="value">R$ {{ number_format($summary['total_net_salary'], 2, ',', '.') }}</div>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Funcionário</th>
                <th>Salário Base</th>
                <th>Proventos</th>
                <th>Descontos</th>
                <th>Líquido</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payrolls as $p)
                <tr>
                    <td>{{ $p->employee_name }}</td>
                    <td class="amount">R$ {{ number_format($p->base_salary, 2, ',', '.') }}</td>
                    <td class="amount">R$ {{ number_format($p->total_earnings - $p->base_salary, 2, ',', '.') }}</td>
                    <td class="amount">R$ {{ number_format($p->total_deductions, 2, ',', '.') }}</td>
                    <td class="amount">R$ {{ number_format($p->net_salary, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>TOTAL</strong></td>
                <td class="amount">R$ {{ number_format($summary['total_base_salary'], 2, ',', '.') }}</td>
                <td class="amount">R$ {{ number_format($summary['total_bonuses'], 2, ',', '.') }}</td>
                <td class="amount">R$ {{ number_format($summary['total_deductions'], 2, ',', '.') }}</td>
                <td class="amount">R$ {{ number_format($summary['total_net_salary'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    <div class="footer">{{ config('app.name') }} - Página {PAGE_NUM} de {PAGE_COUNT}</div>
</body>

</html>
