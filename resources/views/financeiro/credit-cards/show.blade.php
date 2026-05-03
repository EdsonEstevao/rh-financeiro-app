{{-- resources/views/financeiro/credit-cards/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Transação #') }}{{ $transaction->transaction_id }}
            </h2>
            <div class="flex space-x-2">
                @if ($transaction->canBeRefunded())
                    <form action="{{ route('financeiro.credit-cards.refund', $transaction) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Reembolsar esta transação?')"
                            class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 text-sm font-medium">
                            Reembolsar
                        </button>
                    </form>
                @endif
                <a href="{{ route('financeiro.credit-cards.index') }}"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium">
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- Status Card --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        @php
                            $statusColors = [
                                'approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                            ];
                        @endphp
                        <div>
                            <span
                                class="px-3 py-1 text-sm font-semibold rounded-full {{ $statusColors[$transaction->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                                {{ ucfirst($transaction->status) }}
                            </span>
                            <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Card Details --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-4">Dados do
                            Cartão</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Titular</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $transaction->card_holder_name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Bandeira</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 uppercase">
                                    {{ $transaction->card_brand }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Cartão</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $transaction->masked_card_number }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Tipo</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $transaction->card_type === 'credit' ? 'Crédito' : ($transaction->card_type === 'debit' ? 'Débito' : 'Pré-pago') }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Transaction Details --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-4">Detalhes da
                            Transação</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">ID Transação</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 font-mono">
                                    {{ $transaction->transaction_id }}</dd>
                            </div>
                            @if ($transaction->authorization_code)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Autorização</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $transaction->authorization_code }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Parcelas</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $transaction->installments }}x</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Valor Líquido</dt>
                                <dd class="text-sm font-medium text-green-600 dark:text-green-400">R$
                                    {{ number_format($transaction->net_amount, 2, ',', '.') }}</dd>
                            </div>
                            @if ($transaction->fee_amount > 0)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Taxa</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">R$
                                        {{ number_format($transaction->fee_amount, 2, ',', '.') }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Gateway</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $transaction->gateway ?? '---' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            @if ($transaction->description)
                <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Descrição</h3>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $transaction->description }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
