{{-- resources/views/rh/reports/pdfs/employees.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório de Funcionários</title>
    <style>
        @page {
            margin: 100px 25px 50px 25px;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
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

        .filters strong {
            color: #1F2937;
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
            letter-spacing: 0.5px;
        }

        td {
            padding: 8px 10px;
            border-bottom: 1px solid #E5E7EB;
        }

        tr:nth-child(even) {
            background: #F9FAFB;
        }

        .status-active {
            color: #059669;
            font-weight: bold;
        }

        .status-inactive {
            color: #DC2626;
            font-weight: bold;
        }

        .status-vacation {
            color: #D97706;
            font-weight: bold;
        }

        .total-row {
            background: #EFF6FF !important;
            font-weight: bold;
            font-size: 12px;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(0, 0, 0, 0.03);
            pointer-events: none;
            z-index: -1;
        }
    </style>
</head>

<body>
    <div class="watermark">CONFIDENCIAL</div>

    <div class="header">
        <div class="company-name">{{ config('app.name') }}</div>
        <div class="report-title">Relatório de Funcionários</div>
    </div>

    <div class="filters">
        <table style="width: 100%; margin: 0;">
            <tr>
                <td><strong>Departamento:</strong> {{ $filters['department'] }}</td>
                <td><strong>Status:</strong> {{ $filters['status'] }}</td>
                <td><strong>Data/Hora:</strong> {{ $filters['date'] }}</td>
            </tr>
        </table>
    </div>

    <div class="summary-cards">
        <div class="summary-card">
            <div class="label">Total de Funcionários</div>
            <div class="value">{{ $stats['total_employees'] }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Funcionários Ativos</div>
            <div class="value">{{ $stats['active_employees'] }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Folha Salarial Total</div>
            <div class="value">R$ {{ number_format($stats['total_salary'], 2, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>Cargo</th>
                <th>Departamento</th>
                <th>Salário</th>
                <th>Admissão</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
                <tr>
                    <td>{{ $employee->user->name }}</td>
                    <td>{{ $employee->user->cpf }}</td>
                    <td>{{ $employee->position }}</td>
                    <td>{{ $employee->department?->name ?? 'N/A' }}</td>
                    <td>R$ {{ number_format($employee->salary, 2, ',', '.') }}</td>
                    <td>{{ $employee->hire_date->format('d/m/Y') }}</td>
                    <td class="status-{{ $employee->status }}">{{ ucfirst($employee->status) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4"><strong>TOTAL</strong></td>
                <td><strong>R$ {{ number_format($stats['total_salary'], 2, ',', '.') }}</strong></td>
                <td colspan="2">{{ $stats['total_employees'] }} funcionários</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div>Gerado em {{ now()->format('d/m/Y H:i:s') }} - {{ config('app.name') }} - Página {PAGE_NUM} de
            {PAGE_COUNT}</div>
    </div>
</body>

</html>
