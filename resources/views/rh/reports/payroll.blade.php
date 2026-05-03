{{-- resources/views/rh/reports/payroll.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Relatório de Folha de Pagamento') }}</h2>
            <div class="flex space-x-2">
                <a href="{{ route('rh.reports.payroll.pdf', request()->query()) }}"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">Download PDF</a>
                <a href="{{ route('rh.reports.payroll.stream', request()->query()) }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm"
                    target="_blank">Visualizar PDF</a>
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg mb-6 p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div><label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mês</label><select
                            name="month"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}"
                                    {{ ($month ?? now()->month) == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div><label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ano</label><select
                            name="year"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                            @for ($y = now()->year; $y >= 2020; $y--)
                                <option value="{{ $y }}"
                                    {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select></div>
                    <div class="flex space-x-2"><button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm">Filtrar</button></div>
                </form>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Salários Base</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">R$
                        {{ number_format($summary['total_base_salary'] ?? 0, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Descontos Totais</p>
                    <p class="text-2xl font-bold text-red-600">R$
                        {{ number_format($summary['total_deductions'] ?? 0, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Bônus Totais</p>
                    <p class="text-2xl font-bold text-green-600">R$
                        {{ number_format($summary['total_bonuses'] ?? 0, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Líquido Total</p>
                    <p class="text-2xl font-bold text-blue-600">R$
                        {{ number_format($summary['total_net_salary'] ?? 0, 2, ',', '.') }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Funcionário</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Base</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proventos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descontos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Líquido</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($payrolls ?? [] as $p)
                            <tr>
                                <td class="px-6 py-4 text-sm">{{ $p->employee_name }}</td>
                                <td class="px-6 py-4 text-sm">R$ {{ number_format($p->base_salary, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-green-600">R$
                                    {{ number_format($p->total_earnings - $p->base_salary, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-red-600">R$
                                    {{ number_format($p->total_deductions, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm font-bold">R$
                                    {{ number_format($p->net_salary, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
