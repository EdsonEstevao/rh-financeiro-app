{{-- resources/views/funcionario/boletos/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Meus Boletos') }}
            </h2>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Total: {{ auth()->user()->boletos()->count() }} boleto(s)
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                @php
                    $boletosQuery = auth()->user()->boletos();
                    $totalBoletos = $boletosQuery->count();
                    $totalAmount = $boletosQuery->sum('amount');
                    $paidBoletos = $boletosQuery->where('status', 'paid')->count();
                    $paidAmount = $boletosQuery->where('status', 'paid')->sum('amount');
                    $pendingBoletos = $boletosQuery->where('status', 'pending')->count();
                    $pendingAmount = $boletosQuery->where('status', 'pending')->sum('amount');
                    $overdueBoletos = $boletosQuery
                        ->where('status', 'overdue')
                        ->orWhere(function ($q) {
                            $q->where('status', 'pending')->where('due_date', '<', now());
                        })
                        ->count();
                    $overdueAmount = $boletosQuery
                        ->where(function ($q) {
                            $q->where('status', 'overdue')->orWhere(function ($sq) {
                                $sq->where('status', 'pending')->where('due_date', '<', now());
                            });
                        })
                        ->sum('amount');
                @endphp

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total em Boletos</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        R$ {{ number_format($totalAmount, 2, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $totalBoletos }} boleto(s)</div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Pago</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        R$ {{ number_format($paidAmount, 2, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $paidBoletos }} boleto(s)</div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Pendentes</div>
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                        R$ {{ number_format($pendingAmount, 2, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $pendingBoletos }} boleto(s)</div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Vencidos</div>
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                        R$ {{ number_format($overdueAmount, 2, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $overdueBoletos }} boleto(s)</div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select name="status"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                                    Pendente</option>
                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Pago
                                </option>
                                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Vencido
                                </option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>
                                    Cancelado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data
                                Início</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data
                                Fim</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                Filtrar
                            </button>
                            <a href="{{ route('funcionario.boletos') }}"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium">
                                Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Boletos List --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                @php
                    $boletos = auth()
                        ->user()
                        ->boletos()
                        ->when(request('status'), fn($q, $status) => $q->where('status', $status))
                        ->when(request('date_from'), fn($q, $date) => $q->whereDate('due_date', '>=', $date))
                        ->when(request('date_to'), fn($q, $date) => $q->whereDate('due_date', '<=', $date))
                        ->orderBy('due_date', 'desc')
                        ->paginate(12);
                @endphp

                @if ($boletos->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Nº Boleto</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Descrição</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Valor</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Vencimento</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @php
                                    $boletoStatusColor = [
                                        'paid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'pending' =>
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'overdue' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                        'cancelled' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                                    ];
                                @endphp
                                @foreach ($boletos as $boleto)
                                    <tr
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150
                                        @if ($boleto->isOverdue() && $boleto->status !== 'paid') bg-red-50 dark:bg-red-900/20 @endif">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                #{{ $boleto->boleto_number }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Emitido: {{ $boleto->issue_date->format('d/m/Y') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 dark:text-gray-200">
                                                {{ Str::limit($boleto->description, 40) }}
                                            </div>
                                            @if ($boleto->category)
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $boleto->category }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-200">
                                                R$ {{ number_format($boleto->amount, 2, ',', '.') }}
                                            </div>
                                            @if ($boleto->isOverdue() && $boleto->status !== 'paid')
                                                <div class="text-xs text-red-600 dark:text-red-400">
                                                    + Multa/Juros
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-200">
                                                {{ $boleto->due_date->format('d/m/Y') }}
                                            </div>
                                            @if ($boleto->isOverdue() && $boleto->status !== 'paid')
                                                <div class="text-xs text-red-600 dark:text-red-400 font-semibold">
                                                    {{ $boleto->due_date->diffInDays(now()) }} dias atrasado
                                                </div>
                                            @elseif($boleto->status === 'pending')
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    @if ($boleto->due_date->isFuture())
                                                        Faltam {{ now()->diffInDays($boleto->due_date) }} dias
                                                    @else
                                                        Vence hoje
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-3 py-1 text-xs font-semibold rounded-full
                                                {{ $boletoStatusColor[$boleto->status] }}
                                               ">
                                                @if ($boleto->status === 'paid')
                                                    Pago
                                                @elseif($boleto->status === 'pending' && $boleto->isOverdue())
                                                    Vencido
                                                @elseif($boleto->status === 'pending')
                                                    Pendente
                                                @elseif($boleto->status === 'cancelled')
                                                    Cancelado
                                                @else
                                                    {{ ucfirst($boleto->status) }}
                                                @endif
                                            </span>
                                            @if ($boleto->paid_at)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    Pago em {{ $boleto->paid_at->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex justify-end space-x-2">
                                                <a href="{{ route('funcionario.boletos.show', $boleto) }}"
                                                    class="p-1 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 rounded hover:bg-blue-50 dark:hover:bg-blue-900/30"
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
                                                <a href="{{ route('funcionario.boletos.pdf', $boleto) }}"
                                                    class="p-1 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 rounded hover:bg-green-50 dark:hover:bg-green-900/30"
                                                    title="Baixar PDF">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </a>
                                                @if ($boleto->digitable_line)
                                                    <button onclick="copyToClipboard('{{ $boleto->digitable_line }}')"
                                                        class="p-1 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 rounded hover:bg-gray-50 dark:hover:bg-gray-700"
                                                        title="Copiar linha digitável">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $boletos->links() }}
                    </div>
                @else
                    <div class="text-center py-16">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Nenhum boleto encontrado
                        </h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            @if (request()->hasAny(['status', 'date_from', 'date_to']))
                                Nenhum resultado para os filtros selecionados.
                                <a href="{{ route('funcionario.boletos') }}"
                                    class="text-blue-600 dark:text-blue-400 hover:underline">Limpar filtros</a>
                            @else
                                Você não possui boletos emitidos no momento.
                            @endif
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('Linha digitável copiada!');
                }).catch(err => {
                    console.error('Erro ao copiar:', err);
                });
            }
        </script>
    @endpush
</x-app-layout>
