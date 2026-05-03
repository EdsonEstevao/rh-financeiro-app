{{-- resources/views/financeiro/boletos/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Boleto #') }}{{ $boleto->boleto_number }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('financeiro.boletos.edit', $boleto) }}"
                    class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 text-sm font-medium">
                    Editar
                </a>
                <a href="{{ route('financeiro.boletos.pdf', $boleto) }}"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium"
                    target="_blank">
                    Download PDF
                </a>
                <a href="{{ route('financeiro.boletos.index') }}"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium">
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- Status & Amount Card --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <span
                                class="px-3 py-1 text-sm font-semibold rounded-full
                                @if ($boleto->status === 'paid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @elseif($boleto->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @elseif($boleto->status === 'overdue') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                {{ ucfirst($boleto->status === 'paid' ? 'Pago' : ($boleto->status === 'pending' ? 'Pendente' : ($boleto->status === 'overdue' ? 'Vencido' : $boleto->status))) }}
                            </span>
                            <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">
                                Criado em {{ $boleto->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            R$ {{ number_format($boleto->amount, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                {{-- Customer Info --}}
                <div class="md:col-span-1 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-4">Pagador</h3>
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $boleto->payer_name }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $boleto->payer_document }}</p>
                            @if ($boleto->payer_email)
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $boleto->payer_email }}</p>
                            @endif
                            @if ($boleto->payer_phone)
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $boleto->payer_phone }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Boleto Details --}}
                <div class="md:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-4">Detalhes</h3>
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Número do Boleto</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $boleto->boleto_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Nosso Número</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $boleto->our_number ?? '---' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Emissão</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $boleto->issue_date->format('d/m/Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Vencimento</dt>
                                <dd
                                    class="text-sm font-medium @if ($boleto->isOverdue()) text-red-600 dark:text-red-400 @else text-gray-900 dark:text-gray-100 @endif">
                                    {{ $boleto->due_date->format('d/m/Y') }}
                                    @if ($boleto->isOverdue())
                                        <span class="text-xs">({{ $boleto->days_overdue }} dias atraso)</span>
                                    @endif
                                </dd>
                            </div>
                            @if ($boleto->paid_at)
                                <div>
                                    <dt class="text-xs text-gray-500 dark:text-gray-400">Data Pagamento</dt>
                                    <dd class="text-sm font-medium text-green-600 dark:text-green-400">
                                        {{ $boleto->paid_at->format('d/m/Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500 dark:text-gray-400">Valor Pago</dt>
                                    <dd class="text-sm font-medium text-green-600 dark:text-green-400">R$
                                        {{ number_format($boleto->paid_amount, 2, ',', '.') }}</dd>
                                </div>
                            @endif
                            <div class="col-span-2">
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Descrição</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $boleto->description }}</dd>
                            </div>
                            @if ($boleto->instructions)
                                <div class="col-span-2">
                                    <dt class="text-xs text-gray-500 dark:text-gray-400">Instruções</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $boleto->instructions }}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Barcode Section --}}
            @if ($boleto->digitable_line)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-4">Linha
                            Digitável</h3>
                        <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg">
                            <p class="font-mono text-sm text-gray-900 dark:text-gray-100 text-center tracking-wider">
                                {{ $boleto->digitable_line }}
                            </p>
                        </div>
                        <button onclick="copyToClipboard('{{ $boleto->digitable_line }}')"
                            class="mt-3 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                            Copiar linha digitável
                        </button>
                    </div>
                </div>
            @endif

            {{-- Actions --}}
            @if ($boleto->status === 'pending')
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-4">Ações</h3>
                        <div class="flex flex-wrap gap-3">
                            <form action="{{ route('financeiro.boletos.mark-paid', $boleto) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
                                    Marcar como Pago
                                </button>
                            </form>
                            <form action="{{ route('financeiro.boletos.cancel', $boleto) }}" method="POST"
                                onsubmit="return confirm('Tem certeza que deseja cancelar este boleto?')">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium">
                                    Cancelar Boleto
                                </button>
                            </form>
                            <button onclick="if(confirm('Enviar boleto por e-mail?')) { /* lógica de envio */ }"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                Enviar por E-mail
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('Linha digitável copiada!');
                });
            }
        </script>
    @endpush
</x-app-layout>
