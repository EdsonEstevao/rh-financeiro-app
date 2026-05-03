{{-- resources/views/rh/payroll/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Folha de Pagamento') }}</h2>
            <form action="{{ route('rh.payroll.process') }}" method="POST">
                @csrf
                <button type="submit"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">Processar
                    Folha Mensal</button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Summary --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                @php
                    $payrolls = \App\Models\Payroll::whereMonth('period', now()->month)
                        ->whereYear('period', now()->year)
                        ->get();
                    $totalGross = $payrolls->sum('gross_salary');
                    $totalNet = $payrolls->sum('net_salary');
                @endphp
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Total Bruto</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">R$
                        {{ number_format($totalGross, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Total Líquido</p>
                    <p class="text-2xl font-bold text-green-600">R$ {{ number_format($totalNet, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Funcionários Processados</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $payrolls->count() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Mês Referência</p>
                    <p class="text-2xl font-bold text-purple-600">{{ now()->translatedFormat('F/Y') }}</p>
                </div>
            </div>

            {{-- Payroll Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Funcionário</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Salário Base</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Proventos</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Descontos</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Líquido</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($payrolls as $p)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $p->employee_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">R$
                                        {{ number_format($p->base_salary, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">R$
                                        {{ number_format($p->total_earnings - $p->base_salary, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">- R$
                                        {{ number_format($p->total_deductions, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">R$
                                        {{ number_format($p->net_salary, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full @if ($p->status === 'paid') bg-green-100 text-green-800 @else bg-yellow-100 text-yellow-800 @endif">{{ $p->status === 'paid' ? 'Pago' : 'Pendente' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <a href="{{ route('rh.payroll.payslip', $p) }}"
                                            class="text-blue-600 hover:text-blue-800 text-sm">Holerite PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">Nenhuma folha
                                        processada este mês.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
