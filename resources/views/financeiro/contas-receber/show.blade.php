{{-- resources/views/financeiro/contas-receber/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Conta a Receber') }} #{{ $conta->numero_documento }}
            </h2>
            <div class="flex space-x-2">
                @if (in_array($conta->status, ['pendente', 'vencido', 'em_cobranca', 'enviado']))
                    <button onclick="openReceiveModal()"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Registrar Recebimento
                    </button>
                @endif
                @if (!in_array($conta->status, ['recebido', 'cancelado', 'conciliado']))
                    <a href="{{ route('financeiro.contas-receber.edit', $conta) }}"
                        class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 text-sm font-medium">
                        Editar
                    </a>
                @endif
                <a href="{{ route('financeiro.contas-receber.index') }}"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium">
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ showReceiveModal: false }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- Status Banner --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center space-x-3">
                        <span
                            class="px-3 py-1 text-sm font-semibold rounded-full bg-{{ $conta->status_color }}-100 text-{{ $conta->status_color }}-800 dark:bg-{{ $conta->status_color }}-900 dark:text-{{ $conta->status_color }}-200">
                            {{ $conta->status_label }}
                        </span>
                        @if ($conta->isOverdue())
                            <span class="text-red-600 dark:text-red-400 text-sm font-medium">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                {{ $conta->dias_atraso }} dias de atraso
                            </span>
                        @endif
                        @if ($conta->priority === 'urgente')
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                Urgente
                            </span>
                        @endif
                    </div>
                    <div
                        class="text-3xl font-bold {{ $conta->isOverdue() ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">
                        R$ {{ number_format($conta->valor_total, 2, ',', '.') }}
                    </div>
                </div>

                {{-- Progress Bar para contas parceladas --}}
                @if ($conta->total_parcelas > 1)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-500 dark:text-gray-400">Parcela
                                {{ $conta->parcela_atual }}/{{ $conta->total_parcelas }}</span>
                            <span class="text-gray-500 dark:text-gray-400">
                                @php
                                    $parcelasPagas = $conta->parcelas()->where('status', 'recebido')->count();
                                @endphp
                                {{ $parcelasPagas }} recebidas
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full transition-all"
                                style="width: {{ ($parcelasPagas / $conta->total_parcelas) * 100 }}%"></div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Details --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Account Details --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Detalhes da Conta</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Documento</dt>
                                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $conta->numero_documento }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Tipo</dt>
                                <dd class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ ucfirst(str_replace('_', ' ', $conta->tipo)) }}</dd>
                            </div>
                            @if ($conta->forma_pagamento)
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Forma Pagamento</dt>
                                    <dd class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ ucfirst(str_replace('_', ' ', $conta->forma_pagamento)) }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Emissão</dt>
                                <dd class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $conta->data_emissao->format('d/m/Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Vencimento</dt>
                                <dd
                                    class="font-medium {{ $conta->isOverdue() ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">
                                    {{ $conta->data_vencimento->format('d/m/Y') }}
                                    @if (!$conta->isOverdue() && $conta->data_vencimento->isFuture())
                                        <span class="text-xs text-gray-500">(em {{ $conta->dias_ate_vencimento }}
                                            dias)</span>
                                    @endif
                                </dd>
                            </div>
                            @if ($conta->categoria)
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Categoria</dt>
                                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $conta->categoria }}
                                    </dd>
                                </div>
                            @endif
                            @if ($conta->centro_custo)
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Centro de Custo</dt>
                                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $conta->centro_custo }}
                                    </dd>
                                </div>
                            @endif
                            @if ($conta->total_parcelas > 1)
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Parcelas</dt>
                                    <dd class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $conta->parcela_atual }}/{{ $conta->total_parcelas }}</dd>
                                </div>
                            @endif
                            <div class="sm:col-span-2">
                                <dt class="text-gray-500 dark:text-gray-400">Descrição</dt>
                                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $conta->descricao }}</dd>
                            </div>
                            @if ($conta->observacoes)
                                <div class="sm:col-span-2">
                                    <dt class="text-gray-500 dark:text-gray-400">Observações</dt>
                                    <dd class="text-gray-700 dark:text-gray-300">{{ $conta->observacoes }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Values --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Valores</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Valor Original</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">R$
                                    {{ number_format($conta->valor_original, 2, ',', '.') }}</span>
                            </div>
                            @if ($conta->valor_desconto > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Desconto</span>
                                    <span class="font-medium text-green-600 dark:text-green-400">- R$
                                        {{ number_format($conta->valor_desconto, 2, ',', '.') }}</span>
                                </div>
                            @endif
                            @if ($conta->valor_multa > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Multa</span>
                                    <span class="font-medium text-red-600 dark:text-red-400">+ R$
                                        {{ number_format($conta->valor_multa, 2, ',', '.') }}</span>
                                </div>
                            @endif
                            @if ($conta->valor_juros > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Juros</span>
                                    <span class="font-medium text-red-600 dark:text-red-400">+ R$
                                        {{ number_format($conta->valor_juros, 2, ',', '.') }}</span>
                                </div>
                            @endif
                            <div
                                class="flex justify-between text-sm font-bold pt-3 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-gray-700 dark:text-gray-300">Total</span>
                                <span class="text-gray-900 dark:text-gray-100">R$
                                    {{ number_format($conta->valor_total, 2, ',', '.') }}</span>
                            </div>
                            @if ($conta->status === 'recebido')
                                <div class="flex justify-between text-sm pt-2">
                                    <span class="text-green-600 dark:text-green-400">Valor Recebido</span>
                                    <span class="font-bold text-green-600 dark:text-green-400">R$
                                        {{ number_format($conta->valor_recebido, 2, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Parcelas --}}
                    @if ($conta->parcelas && $conta->parcelas->count() > 0)
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Parcelas</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-gray-500 dark:text-gray-400">
                                            <th class="pb-2">Parcela</th>
                                            <th class="pb-2">Vencimento</th>
                                            <th class="pb-2">Valor</th>
                                            <th class="pb-2">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($conta->parcelas as $parcela)
                                            <tr class="border-t dark:border-gray-700">
                                                <td class="py-2">
                                                    {{ $parcela->parcela_atual }}/{{ $parcela->total_parcelas }}</td>
                                                <td class="py-2">{{ $parcela->data_vencimento->format('d/m/Y') }}
                                                </td>
                                                <td class="py-2">R$
                                                    {{ number_format($parcela->valor_total, 2, ',', '.') }}</td>
                                                <td class="py-2">
                                                    <span
                                                        class="px-2 py-1 text-xs rounded-full bg-{{ $parcela->status_color }}-100 text-{{ $parcela->status_color }}-800">
                                                        {{ $parcela->status_label }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Cobrança --}}
                    @if ($conta->enviou_cobranca)
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Histórico de
                                Cobrança</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Tentativas</span>
                                    <span class="font-medium">{{ $conta->tentativas_cobranca }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Última Cobrança</span>
                                    <span
                                        class="font-medium">{{ $conta->ultima_cobranca?->format('d/m/Y H:i') ?? '---' }}</span>
                                </div>
                                @if ($conta->historico_cobranca)
                                    @php $historico = is_string($conta->historico_cobranca) ? json_decode($conta->historico_cobranca, true) : $conta->historico_cobranca; @endphp
                                    @if (is_array($historico))
                                        <div class="mt-3 space-y-1">
                                            @foreach (array_slice($historico, -5) as $entry)
                                                <div class="text-xs text-gray-500 border-l-2 border-blue-300 pl-2">
                                                    {{ $entry['data'] ?? '' }} - {{ $entry['acao'] ?? '' }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Client Info --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Cliente</h3>
                        <div class="flex items-center mb-4">
                            <div
                                class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-lg">
                                {{ strtoupper(substr($conta->cliente_nome, 0, 2)) }}
                            </div>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $conta->cliente_nome }}</p>
                                @if ($conta->cliente)
                                    <p class="text-xs text-gray-500">{{ $conta->cliente->email }}</p>
                                @endif
                            </div>
                        </div>
                        <dl class="space-y-2 text-sm">
                            @if ($conta->cliente_documento)
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Documento</dt>
                                    <dd class="font-medium">{{ $conta->cliente_documento }}</dd>
                                </div>
                            @endif
                            @if ($conta->cliente_email)
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Email</dt>
                                    <dd class="font-medium truncate ml-2">{{ $conta->cliente_email }}</dd>
                                </div>
                            @endif
                            @if ($conta->cliente_telefone)
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Telefone</dt>
                                    <dd class="font-medium">{{ $conta->cliente_telefone }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Commission Info --}}
                    @if ($conta->has_commission)
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Comissão</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Consultor</dt>
                                    <dd class="font-medium">{{ $conta->consultant?->name ?? '---' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Percentual</dt>
                                    <dd class="font-medium">{{ $conta->commission_percentage }}%</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Valor</dt>
                                    <dd class="font-medium text-green-600">R$
                                        {{ number_format($conta->commission_amount, 2, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Status</dt>
                                    <dd>
                                        <span
                                            class="px-2 py-1 text-xs rounded-full {{ $conta->commission_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $conta->commission_paid ? 'Paga' : 'Pendente' }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    @endif

                    {{-- Quick Actions --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ações</h3>
                        <div class="space-y-2">
                            @if (in_array($conta->status, ['pendente', 'vencido', 'em_cobranca']))
                                <button onclick="openReceiveModal()"
                                    class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
                                    Registrar Recebimento
                                </button>
                                <form action="{{ route('financeiro.contas-receber.enviar-cobranca', $conta) }}"
                                    method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 text-sm font-medium">
                                        Enviar Cobrança
                                    </button>
                                </form>
                            @endif
                            @if (!in_array($conta->status, ['recebido', 'cancelado', 'conciliado']))
                                <a href="{{ route('financeiro.contas-receber.edit', $conta) }}"
                                    class="block w-full px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 text-sm font-medium text-center">
                                    Editar Conta
                                </a>
                            @endif
                            @if (in_array($conta->status, ['pendente', 'vencido']))
                                <form action="{{ route('financeiro.contas-receber.cancel', $conta) }}" method="POST"
                                    onsubmit="return prompt('Motivo do cancelamento:') ? true : false">
                                    @csrf
                                    <input type="hidden" name="motivo" id="cancel-motivo">
                                    <button type="submit"
                                        onclick="document.getElementById('cancel-motivo').value = prompt('Motivo do cancelamento:')"
                                        class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium">
                                        Cancelar Conta
                                    </button>
                                </form>
                            @endif
                            @if ($conta->boleto_id)
                                <a href="{{ route('financeiro.boletos.show', $conta->boleto_id) }}"
                                    class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium text-center">
                                    Ver Boleto
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Audit Info --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">Informações
                        </h3>
                        <dl class="space-y-2 text-xs">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Criado por</dt>
                                <dd class="font-medium">{{ $conta->createdBy?->name ?? 'Sistema' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Criado em</dt>
                                <dd class="font-medium">{{ $conta->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            @if ($conta->receivedBy)
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Recebido por</dt>
                                    <dd class="font-medium">{{ $conta->receivedBy->name }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Atualizado em</dt>
                                <dd class="font-medium">{{ $conta->updated_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Comprovante --}}
            @if ($conta->comprovante_path)
                <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Comprovante de Recebimento
                    </h3>
                    <a href="{{ Storage::url($conta->comprovante_path) }}" target="_blank"
                        class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:underline">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Visualizar comprovante
                    </a>
                </div>
            @endif
        </div>

        {{-- Receive Payment Modal --}}
        @if (in_array($conta->status, ['pendente', 'vencido', 'em_cobranca', 'enviado']))
            <div x-show="showReceiveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="showReceiveModal = false"></div>
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full shadow-xl p-6"
                        @click.away="showReceiveModal = false">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Registrar Recebimento
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Conta #{{ $conta->numero_documento }} - {{ $conta->cliente_nome }}
                            </p>
                        </div>
                        <form action="{{ route('financeiro.contas-receber.mark-received', $conta) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor
                                        Recebido *</label>
                                    <div class="relative">
                                        <span
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">R$</span>
                                        <input type="number" name="valor_recebido" step="0.01"
                                            value="{{ $conta->valor_atualizado }}" required
                                            class="w-full pl-10 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    </div>
                                    @if ($conta->isOverdue())
                                        <p class="text-xs text-red-500 mt-1">
                                            Valor atualizado: R$
                                            {{ number_format($conta->valor_atualizado, 2, ',', '.') }}
                                        </p>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data
                                        Recebimento *</label>
                                    <input type="date" name="data_recebimento"
                                        value="{{ now()->format('Y-m-d') }}" required
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comprovante</label>
                                    <input type="file" name="comprovante" accept=".pdf,.jpg,.jpeg,.png"
                                        class="w-full text-sm text-gray-500 dark:text-gray-400
                                              file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 
                                              file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 
                                              hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-200">
                                    <p class="text-xs text-gray-500 mt-1">PDF, JPG ou PNG (máx. 5MB)</p>
                                </div>
                            </div>
                            <div class="flex justify-end space-x-3 mt-6">
                                <button type="button" @click="showReceiveModal = false"
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium transition">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Confirmar Recebimento
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function openReceiveModal() {
                const el = document.querySelector('[x-data]');
                if (el && el.__x) {
                    el.__x.$data.showReceiveModal = true;
                }
            }
        </script>
    @endpush
</x-app-layout>
