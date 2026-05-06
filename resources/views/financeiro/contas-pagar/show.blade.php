<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Conta a Pagar') }} #{{ $conta->numero_documento }}
            </h2>
            <div class="flex space-x-2">
                @if (in_array($conta->status, ['pendente', 'aprovado', 'vencido']))
                    <button onclick="openPayModal()"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">Registrar
                        Pagamento</button>
                @endif
                <a href="{{ route('financeiro.contas-pagar.index') }}"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm">Voltar</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- Status Banner --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $conta->status_color_class }}">
                            {{ $conta->status_label }}
                        </span>
                        @if ($conta->isOverdue())
                            <span class="ml-2 text-red-600 text-sm font-medium">{{ $conta->dias_atraso }} dias de
                                atraso</span>
                        @endif
                    </div>
                    <div
                        class="text-3xl font-bold {{ $conta->isOverdue() ? 'text-red-600' : 'text-gray-900 dark:text-gray-100' }}">
                        R$ {{ number_format($conta->valor_total, 2, ',', '.') }}
                    </div>
                </div>
            </div>

            {{-- Details --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Detalhes</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Documento</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $conta->numero_documento }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Tipo</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $conta->tipo_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Beneficiário</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $conta->beneficiario_nome }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">CPF/CNPJ</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $conta->beneficiario_documento ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Emissão</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $conta->data_emissao->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Vencimento</dt>
                        <dd
                            class="font-medium {{ $conta->isOverdue() ? 'text-red-600' : 'text-gray-900 dark:text-gray-100' }}">
                            {{ $conta->data_vencimento->format('d/m/Y') }}
                        </dd>
                    </div>
                    @if ($conta->data_pagamento)
                        <div>
                            <dt class="text-gray-500">Data Pagamento</dt>
                            <dd class="font-medium text-green-600">{{ $conta->data_pagamento->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Valor Pago</dt>
                            <dd class="font-medium text-green-600">R$
                                {{ number_format($conta->valor_pago, 2, ',', '.') }}</dd>
                        </div>
                    @endif
                    <div class="md:col-span-2">
                        <dt class="text-gray-500">Descrição</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $conta->descricao }}</dd>
                    </div>
                    @if ($conta->observacoes)
                        <div class="md:col-span-2">
                            <dt class="text-gray-500">Observações</dt>
                            <dd class="text-gray-700 dark:text-gray-300">{{ $conta->observacoes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Comprovante --}}
            @if ($conta->comprovante_path)
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Comprovante</h3>
                    <a href="{{ Storage::url($conta->comprovante_path) }}" target="_blank"
                        class="text-blue-600 hover:underline">Visualizar comprovante</a>
                </div>
            @endif
        </div>
    </div>

    {{-- Payment Modal --}}
    @if (in_array($conta->status, ['pendente', 'aprovado', 'vencido']))
        <div x-data="{ showPayModal: false }" x-show="showPayModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            style="display:none;">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showPayModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full shadow-xl p-6"
                    @click.away="showPayModal = false">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Registrar Pagamento</h3>
                    <form action="{{ route('financeiro.contas-pagar.mark-paid', $conta) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor
                                    Pago *</label>
                                <input type="number" name="valor_pago" step="0.01"
                                    value="{{ $conta->valor_total }}" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data
                                    Pagamento *</label>
                                <input type="date" name="data_pagamento" value="{{ now()->format('Y-m-d') }}"
                                    required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comprovante</label>
                                <input type="file" name="comprovante"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" @click="showPayModal = false"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md">Cancelar</button>
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Confirmar
                                Pagamento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            function openPayModal() {
                document.querySelector('[x-data]').__x.$data.showPayModal = true;
            }
        </script>
    @endpush
</x-app-layout>
