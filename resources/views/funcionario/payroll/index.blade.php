{{-- resources/views/funcionario/payroll/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Meus Holerites') }}
            </h2>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ auth()->user()->employee?->position ?? 'Funcionário' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @php
                    $employee = auth()->user()->employee;
                    $latestPayroll = $employee
                        ? \App\Models\Payroll::where('employee_id', $employee->id)->latest()->first()
                        : null;
                    $yearTotal = $employee
                        ? \App\Models\Payroll::where('employee_id', $employee->id)
                            ->where('year', now()->year)
                            ->sum('net_salary')
                        : 0;
                    $payrollCount = $employee ? \App\Models\Payroll::where('employee_id', $employee->id)->count() : 0;
                @endphp

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Último Salário Líquido</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        @if ($latestPayroll)
                            R$ {{ number_format($latestPayroll->net_salary, 2, ',', '.') }}
                        @else
                            ---
                        @endif
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        @if ($latestPayroll)
                            {{ \Carbon\Carbon::createFromDate($latestPayroll->year, $latestPayroll->month, 1)->translatedFormat('F/Y') }}
                        @else
                            Nenhum registro
                        @endif
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Recebido no Ano</div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        R$ {{ number_format($yearTotal, 2, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ now()->year }}
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total de Holerites</div>
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        {{ $payrollCount }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Desde {{ $employee?->hire_date->format('d/m/Y') ?? '---' }}
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ano</label>
                            <select name="year"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                @for ($y = now()->year; $y >= now()->subYears(5)->year; $y--)
                                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                        {{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                            <select name="type"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                <option value="monthly" {{ request('type') === 'monthly' ? 'selected' : '' }}>Mensal
                                </option>
                                <option value="thirteenth" {{ request('type') === 'thirteenth' ? 'selected' : '' }}>13º
                                    Salário</option>
                                <option value="vacation" {{ request('type') === 'vacation' ? 'selected' : '' }}>Férias
                                </option>
                                <option value="bonus" {{ request('type') === 'bonus' ? 'selected' : '' }}>Bônus
                                </option>
                            </select>
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                Filtrar
                            </button>
                            <a href="{{ route('funcionario.payroll') }}"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium">
                                Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Payroll List --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                @php
                    $payrolls = $employee
                        ? \App\Models\Payroll::where('employee_id', $employee->id)
                            ->when(request('year'), fn($q, $year) => $q->where('year', $year))
                            ->when(request('type'), fn($q, $type) => $q->where('type', $type))
                            ->orderBy('year', 'desc')
                            ->orderBy('month', 'desc')
                            ->paginate(12)
                        : collect();
                @endphp

                @if ($payrolls->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Período</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Tipo</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Salário Base</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Descontos</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Proventos</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Líquido</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($payrolls as $payroll)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                {{ \Carbon\Carbon::createFromDate($payroll->year, $payroll->month, 1)->translatedFormat('F/Y') }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Ref: {{ $payroll->reference_number }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full
                                                @if ($payroll->type === 'monthly') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                @elseif($payroll->type === 'thirteenth') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                @elseif($payroll->type === 'vacation') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @else bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 @endif">
                                                @if ($payroll->type === 'monthly')
                                                    Mensal
                                                @elseif($payroll->type === 'thirteenth')
                                                    13º Salário
                                                @elseif($payroll->type === 'vacation')
                                                    Férias
                                                @else
                                                    {{ ucfirst($payroll->type) }}
                                                @endif
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                            R$ {{ number_format($payroll->base_salary, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 dark:text-red-400">
                                            - R$ {{ number_format($payroll->total_deductions, 2, ',', '.') }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-green-600 dark:text-green-400">
                                            + R$
                                            {{ number_format($payroll->total_earnings - $payroll->base_salary, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-bold text-green-600 dark:text-green-400">
                                                R$ {{ number_format($payroll->net_salary, 2, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full
                                                @if ($payroll->status === 'paid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @elseif($payroll->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                {{ $payroll->status === 'paid' ? 'Pago' : ($payroll->status === 'pending' ? 'Pendente' : ucfirst($payroll->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <div class="flex justify-end space-x-2">
                                                <a href="{{ route('funcionario.payroll.show', $payroll) }}"
                                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                                                    title="Visualizar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                                <a href="{{ route('funcionario.payroll.payslip', $payroll) }}"
                                                    class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300"
                                                    title="Baixar PDF">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $payrolls->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Nenhum holerite
                            encontrado</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            @if (request()->hasAny(['year', 'type']))
                                Nenhum resultado para os filtros selecionados.
                                <a href="{{ route('funcionario.payroll') }}"
                                    class="text-blue-600 dark:text-blue-400 hover:underline">Limpar filtros</a>
                            @else
                                Seus holerites aparecerão aqui quando forem processados.
                            @endif
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
