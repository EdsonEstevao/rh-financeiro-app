{{-- resources/views/financeiro/reports/credit-cards.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Relatório de Cartões') }}</h2>
            <div class="flex space-x-2">
                <a href="{{ route('financeiro.reports.credit-cards.pdf', request()->query()) }}"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">PDF</a>
                <a href="{{ route('financeiro.reports.credit-cards.stream', request()->query()) }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm"
                    target="_blank">Visualizar</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg mb-6 p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Todos</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Aprovada
                            </option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejeitada
                            </option>
                            <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Reembolsada
                            </option>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">De</label><input
                            type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    </div>
                    <div><label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Até</label><input
                            type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    </div>
                    <div class="flex space-x-2"><button type="submit"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md text-sm">Filtrar</button><a
                            href="{{ route('financeiro.reports.credit-cards') }}"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md text-sm">Limpar</a>
                    </div>
                </form>
            </div>

            {{-- Summary --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 text-center">
                    <p class="text-xs text-gray-500">Total Transações</p>
                    <p class="text-xl font-bold">{{ $summary['total_transactions'] ?? 0 }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 text-center">
                    <p class="text-xs text-gray-500">Valor Total</p>
                    <p class="text-xl font-bold text-blue-600">R$
                        {{ number_format($summary['total_amount'] ?? 0, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 text-center">
                    <p class="text-xs text-gray-500">Aprovado</p>
                    <p class="text-xl font-bold text-green-600">R$
                        {{ number_format($summary['approved_amount'] ?? 0, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 text-center">
                    <p class="text-xs text-gray-500">Ticket Médio</p>
                    <p class="text-xl font-bold text-purple-600">R$
                        {{ number_format($summary['average_ticket'] ?? 0, 2, ',', '.') }}</p>
                </div>
            </div>

            {{-- By Card Brand --}}
            @if (isset($byCardBrand) && $byCardBrand->count())
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-semibold mb-4">Por Bandeira</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach ($byCardBrand as $brand => $data)
                            <div class="text-center p-4 border rounded-lg">
                                <p class="text-sm font-semibold uppercase">{{ $brand }}</p>
                                <p class="text-lg font-bold">{{ $data['count'] }}</p>
                                <p class="text-xs text-gray-500">R$ {{ number_format($data['total'], 2, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transação</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bandeira</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($transactions ?? [] as $t)
                            <tr>
                                <td class="px-6 py-4 text-sm font-mono text-xs">{{ $t->transaction_id }}</td>
                                <td class="px-6 py-4 text-sm">{{ $t->customer_name }}</td>
                                <td class="px-6 py-4 text-sm uppercase">{{ $t->card_brand }}</td>
                                <td class="px-6 py-4 text-sm">R$ {{ number_format($t->amount, 2, ',', '.') }}</td>
                                <td class="px-6 py-4"><span
                                        class="px-2 py-1 text-xs rounded-full {{ $t->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ ucfirst($t->status) }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">Nenhuma transação
                                    encontrada</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
