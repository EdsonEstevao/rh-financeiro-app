{{-- resources/views/financeiro/reports/cash-flow.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Fluxo de Caixa') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('financeiro.reports.cash-flow.pdf', request()->query()) }}"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">PDF</a>
                <a href="{{ route('financeiro.reports.cash-flow.stream', request()->query()) }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm"
                    target="_blank">Visualizar</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg mb-6 p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mês</label>
                        <select name="month"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ ($month ?? now()->month) == $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ano</label>
                        <select name="year"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            @for ($y = now()->year; $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>
                                    {{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="flex space-x-2"><button type="submit"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md text-sm">Filtrar</button></div>
                </form>
            </div>

            {{-- Summary --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Total Receitas</p>
                    <p class="text-2xl font-bold text-green-600">R$ {{ number_format($totalIncome ?? 0, 2, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Boletos Pagos</p>
                    <p class="text-2xl font-bold text-blue-600">R$ {{ number_format($boletosTotal ?? 0, 2, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Cartões Aprovados</p>
                    <p class="text-2xl font-bold text-purple-600">R$ {{ number_format($cardsTotal ?? 0, 2, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- Daily Balance --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @php $runningTotal = 0; @endphp
                        @forelse($incomes ?? [] as $income)
                            @php $runningTotal += $income['amount']; @endphp
                            <tr>
                                <td class="px-6 py-4 text-sm">
                                    {{ Carbon\Carbon::parse($income['date'])->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-sm">{{ $income['description'] }}</td>
                                <td class="px-6 py-4"><span
                                        class="px-2 py-1 text-xs rounded-full {{ $income['type'] === 'Boleto' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">{{ $income['type'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-medium">R$
                                    {{ number_format($income['amount'], 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">Nenhuma receita no
                                    período</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700 font-semibold">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right">Total Acumulado:</td>
                            <td class="px-6 py-3 text-right">R$ {{ number_format($runningTotal, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
