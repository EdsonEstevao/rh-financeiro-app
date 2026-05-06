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
                            <option value="cancelado" {{ request('status') === 'cancelado' ? 'selected' : '' }}>Canceladas
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                        <select name="tipo"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            <option value="">Todos</option>
                            @foreach ($tipos ?? [] as $value => $label)
                                <option value="{{ $value }}" {{ request('tipo') === $value ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">De</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Até</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md text-sm">Filtrar</button>
                        <a href="{{ route('financeiro.contas-pagar.index') }}"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md text-sm">Limpar</a>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Documento
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fornecedor
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimento
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($contas as $conta)
                                <tr
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700 transition {{ $conta->isOverdue() ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        {{ $conta->numero_documento }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $conta->beneficiario_nome }}
                                    </td>
                                    <td class="px-6 py-4 text-sm max-w-xs truncate">{{ $conta->descricao }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">R$
                                        {{ number_format($conta->valor_total, 2, ',', '.') }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm {{ $conta->isOverdue() ? 'text-red-600 font-semibold' : '' }}">
                                        {{ $conta->data_vencimento->format('d/m/Y') }}
                                        @if ($conta->isOverdue())
                                            <div class="text-xs">({{ $conta->dias_atraso }} dias)</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full {{ $conta->status_color_class }}">
                                            {{ $conta->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex justify-end space-x-1">
                                            <a href="{{ route('financeiro.contas-pagar.show', $conta) }}"
                                                class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg"
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
                                            @if (in_array($conta->status, ['pendente', 'aprovado', 'vencido']))
                                                <button onclick="openPayModal({{ $conta->id }})"
                                                    class="p-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg"
                                                    title="Pagar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">Nenhuma conta a
                                        pagar encontrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $contas->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
