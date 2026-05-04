{{-- resources/views/financeiro/reports/boletos.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Relatório de Boletos') }}</h2>
            <div class="flex space-x-2">
                <a href="{{ route('financeiro.reports.boletos.pdf', request()->query()) }}"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">PDF</a>
                <a href="{{ route('financeiro.reports.boletos.stream', request()->query()) }}"
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
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Pago</option>
                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Vencido</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado
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
                    <div class="flex space-x-2">
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md text-sm">Filtrar</button>
                        <a href="{{ route('financeiro.reports.boletos') }}"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md text-sm">Limpar</a>
                    </div>
                </form>
            </div>

            {{-- Summary --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 text-center">
                    <p class="text-xs text-gray-500">Total</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $summary['total_count'] ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-500">R$ {{ number_format($summary['total_amount'] ?? 0, 2, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 text-center">
                    <p class="text-xs text-gray-500">Pagos</p>
                    <p class="text-xl font-bold text-green-600">{{ $summary['paid_count'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">R$ {{ number_format($summary['paid_amount'] ?? 0, 2, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 text-center">
                    <p class="text-xs text-gray-500">Pendentes</p>
                    <p class="text-xl font-bold text-yellow-600">{{ $summary['pending_count'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">R$
                        {{ number_format($summary['pending_amount'] ?? 0, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 text-center">
                    <p class="text-xs text-gray-500">Vencidos</p>
                    <p class="text-xl font-bold text-red-600">{{ $summary['overdue_count'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">R$
                        {{ number_format($summary['overdue_amount'] ?? 0, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 text-center">
                    <p class="text-xs text-gray-500">Cancelados</p>
                    <p class="text-xl font-bold text-gray-600">{{ $summary['cancelled_count'] ?? 0 }}</p>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Boleto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pagamento</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($boletos ?? [] as $boleto)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium">#{{ $boleto->boleto_number }}</td>
                                <td class="px-6 py-4 text-sm">{{ $boleto->user->name ?? $boleto->payer_name }}</td>
                                <td class="px-6 py-4 text-sm">R$ {{ number_format($boleto->amount, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm">{{ $boleto->due_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4"><span
                                        class="px-2 py-1 text-xs rounded-full {{ $boleto->status === 'paid' ? 'bg-green-100 text-green-800' : ($boleto->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">{{ ucfirst($boleto->status) }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $boleto->paid_at?->format('d/m/Y') ?? '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">Nenhum boleto encontrado
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
