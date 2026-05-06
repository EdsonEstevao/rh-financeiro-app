<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Contas a Pagar') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('financeiro.contas-pagar.create') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nova Conta
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Total Pendente</p>
                    <p class="text-2xl font-bold text-yellow-600">R$
                        {{ number_format($stats['valor_pendente'] ?? 0, 2, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['total_pendente'] ?? 0 }} contas</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Vencidas</p>
                    <p class="text-2xl font-bold text-red-600">R$
                        {{ number_format($stats['valor_vencido'] ?? 0, 2, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['total_vencido'] ?? 0 }} contas</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Pago no Mês</p>
                    <p class="text-2xl font-bold text-green-600">R$
                        {{ number_format($stats['valor_pago_mes'] ?? 0, 2, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['total_pago_mes'] ?? 0 }} contas</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">A Vencer (30 dias)</p>
                    <p class="text-2xl font-bold text-blue-600">R$
                        {{ number_format($stats['valor_a_vencer_30_dias'] ?? 0, 2, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['a_vencer_30_dias'] ?? 0 }} contas</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg mb-6 p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Todos</option>
                            <option value="pendente" {{ request('status') === 'pendente' ? 'selected' : '' }}>Pendente
                            </option>
                            <option value="vencidas" {{ request('status') === 'vencidas' ? 'selected' : '' }}>Vencidas
                            </option>
                            <option value="pago" {{ request('status') === 'pago' ? 'selected' : '' }}>Pagas</option>
                            <option value="cancelado" {{ request('status') === 'cancelado' ? 'selected' : '' }}>
                                Canceladas
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fornecedor</label>
                        <select name="fornecedor_id"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Todos</option>
                            @foreach ($fornecedores as $fornecedor)
                                <option value="{{ $fornecedor->id }}"
                                    {{ request('fornecedor_id') == $fornecedor->id ? 'selected' : '' }}>
                                    {{ $fornecedor->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data de
                            Vencimento</label>
                        <div class="flex space-x-2">
                            <input type="date" name="data_vencimento_inicio"
                                value="{{ request('data_vencimento_inicio') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                            <span class="text-gray-500">até</span>
                            <input type="date" name="data_vencimento_fim"
                                value="{{ request('data_vencimento_fim') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor</label>
                        <div class="flex space-x-2">
                            <input type="number" step="0.01" name="valor_min" value="{{ request('valor_min') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                            <span class="text-gray-500">até</span>
                            <input type="number" step="0.01" name="valor_max" value="{{ request('valor_max') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                    </div>
                    <div>
                        <button type="submit"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Contas a Pagar Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500">
                                <th class="pb-2">Documento</th>
                                <th class="pb-2">Fornecedor</th>
                                <th class="pb-2">Valor</th>
                                <th class="pb-2">Vencimento</th>
                                <th class="pb-2">Status</th>
                                <th class="pb-2">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($contasPagar as $contaPagar)
                                <tr
                                    class="text-gray-500 border-b border-gray-100 dark:border-gray-700 dark:text-gray-400">
                                    <td class="py-2">
                                        {{ $contaPagar->documento }}
                                    </td>
                                    <td class="py-2">
                                        {{ $contaPagar->fornecedor->nome }}
                                    </td>
                                    <td class="py-2">
                                        {{ number_format($contaPagar->valor, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2">
                                        {{ $contaPagar->data_vencimento }}
                                    </td>
                                    <td class="py-2"></td>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full {{ $contaPagar->status === 'pago' ? 'bg-green-100 text-green-800' : ($contaPagar->status === 'vencida' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $contaPagar->status_label }}
                                    </span>
                                    <td class="py-2">
                                        <a href="{{ route('financeiro.contas-pagar.show', $contaPagar->id) }}"
                                            class="text-blue-600 hover:text-blue-900 font-medium">
                                            Detalhes
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-gray-500">
                                        Nenhuma conta a pagar encontrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $contasPagar->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
