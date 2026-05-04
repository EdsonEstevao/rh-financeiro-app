{{-- resources/views/financeiro/credit-cards/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Transações Cartão de Crédito') }}
            </h2>
            {{-- <a href="{{ route('financeiro.credit-cards.process') }}" --}}
            <a href="{{ route('financeiro.credit-cards.create') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-150 text-sm font-medium flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nova Transação
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                @php
                    $todayTransactions = \App\Models\CreditCardTransaction::whereDate('created_at', today());
                    $monthTransactions = \App\Models\CreditCardTransaction::whereMonth('created_at', now()->month);
                @endphp

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Transações Hoje</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $todayTransactions->count() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">R$
                        {{ number_format($todayTransactions->sum('amount'), 2, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Transações no Mês</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $monthTransactions->count() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">R$
                        {{ number_format($monthTransactions->sum('amount'), 2, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Aprovadas</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ $monthTransactions->where('status', 'approved')->count() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">R$
                        {{ number_format($monthTransactions->where('status', 'approved')->sum('amount'), 2, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ticket Médio</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        R$
                        {{ number_format($monthTransactions->count() > 0 ? $monthTransactions->avg('amount') : 0, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">No mês atual</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select name="status"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>
                                    Aprovada</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                                    Pendente</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>
                                    Rejeitada</option>
                                <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>
                                    Reembolsada</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bandeira</label>
                            <select name="card_brand"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">Todas</option>
                                <option value="visa" {{ request('card_brand') === 'visa' ? 'selected' : '' }}>Visa
                                </option>
                                <option value="mastercard"
                                    {{ request('card_brand') === 'mastercard' ? 'selected' : '' }}>Mastercard</option>
                                <option value="elo" {{ request('card_brand') === 'elo' ? 'selected' : '' }}>Elo
                                </option>
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
                        <div class="flex items-end space-x-2">
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                Filtrar
                            </button>
                            <a href="{{ route('financeiro.credit-cards.index') }}"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium">
                                Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Transactions Table --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                @php
                    $transactions = \App\Models\CreditCardTransaction::with('user')
                        ->when(request('status'), fn($q, $s) => $q->where('status', $s))
                        ->when(request('card_brand'), fn($q, $b) => $q->where('card_brand', $b))
                        ->when(request('date_from'), fn($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when(request('date_to'), fn($q, $d) => $q->whereDate('created_at', '<=', $d))
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);
                @endphp

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Transação</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Cliente</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Cartão</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Valor</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Parcelas</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Data</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @php
                                $statusColors = [
                                    'approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                    'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                    'pending' =>
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                    'refunded' =>
                                        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                    'chargeback' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                ];

                            @endphp
                            @forelse($transactions as $transaction)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="text-xs font-mono text-gray-900 dark:text-gray-200">{{ $transaction->transaction_id }}</span>
                                        @if ($transaction->authorization_code)
                                            <div class="text-xs text-gray-500">Aut:
                                                {{ $transaction->authorization_code }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                            {{ $transaction->customer_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $transaction->customer_document }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span
                                                class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mr-2">{{ $transaction->card_brand }}</span>
                                            <span
                                                class="text-sm text-gray-900 dark:text-gray-200">{{ $transaction->masked_card_number }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-200">R$
                                            {{ number_format($transaction->amount, 2, ',', '.') }}</span>
                                        @if ($transaction->fee_amount > 0)
                                            <div class="text-xs text-gray-500">Taxa: R$
                                                {{ number_format($transaction->fee_amount, 2, ',', '.') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                        @if ($transaction->installments > 1)
                                            {{ $transaction->installments }}x R$
                                            {{ number_format($transaction->installment_value, 2, ',', '.') }}
                                        @else
                                            À vista
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$transaction->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                                            @if ($transaction->status === 'approved')
                                                Aprovada
                                            @elseif($transaction->status === 'refunded')
                                                Reembolsada
                                            @elseif($transaction->status === 'rejected')
                                                Rejeitada
                                            @elseif($transaction->status === 'chargeback')
                                                Chargeback
                                            @else
                                                {{ ucfirst($transaction->status) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $transaction->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex justify-end space-x-1">
                                            <a href="{{ route('financeiro.credit-cards.show', $transaction) }}"
                                                class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg"
                                                title="Detalhes">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            @if ($transaction->canBeRefunded())
                                                <form
                                                    action="{{ route('financeiro.credit-cards.refund', $transaction) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        onclick="return confirm('Reembolsar esta transação?')"
                                                        class="p-2 text-yellow-600 dark:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-900/30 rounded-lg"
                                                        title="Reembolsar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8"
                                        class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        Nenhuma transação encontrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
