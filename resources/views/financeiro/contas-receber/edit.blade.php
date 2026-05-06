{{-- resources/views/financeiro/contas-receber/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Editar Conta a Receber') }} #{{ $conta->numero_documento }}
            </h2>
            <a href="{{ route('financeiro.contas-receber.show', $conta) }}"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium">
                ← Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('financeiro.contas-receber.update', $conta) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Status Alert --}}
                @if (in_array($conta->status, ['recebido', 'cancelado', 'conciliado']))
                    <div
                        class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                Esta conta está com status <strong>{{ $conta->status_label }}</strong>.
                                Alguns campos podem estar bloqueados para edição.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Dados da Conta --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dados da Conta</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo
                                *</label>
                            <select name="tipo" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                {{ in_array($conta->status, ['recebido', 'cancelado']) ? 'disabled' : '' }}>
                                @foreach ($tipos ?? [] as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('tipo', $conta->tipo) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Forma
                                Pagamento</label>
                            <select name="forma_pagamento"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                {{ in_array($conta->status, ['recebido', 'cancelado']) ? 'disabled' : '' }}>
                                <option value="">Selecione</option>
                                @foreach ($formasPagamento ?? [] as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('forma_pagamento', $conta->forma_pagamento) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente</label>
                            <select name="cliente_id"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                {{ in_array($conta->status, ['recebido', 'cancelado']) ? 'disabled' : '' }}>
                                <option value="">Selecione</option>
                                @foreach ($clientes ?? [] as $cliente)
                                    <option value="{{ $cliente->id }}"
                                        {{ old('cliente_id', $conta->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome Cliente
                                *</label>
                            <input type="text" name="cliente_nome"
                                value="{{ old('cliente_nome', $conta->cliente_nome) }}" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('cliente_nome')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CPF/CNPJ</label>
                            <input type="text" name="cliente_documento"
                                value="{{ old('cliente_documento', $conta->cliente_documento) }}"
                                x-mask="999.999.999-99"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email" name="cliente_email"
                                value="{{ old('cliente_email', $conta->cliente_email) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor (R$)
                                *</label>
                            <input type="number" name="valor_original"
                                value="{{ old('valor_original', $conta->valor_original) }}" step="0.01"
                                min="0.01" required
                                {{ in_array($conta->status, ['recebido', 'cancelado']) ? 'readonly' : '' }}
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('valor_original')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor
                                Desconto</label>
                            <input type="number" name="valor_desconto"
                                value="{{ old('valor_desconto', $conta->valor_desconto) }}" step="0.01"
                                min="0"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor
                                Multa</label>
                            <input type="number" name="valor_multa"
                                value="{{ old('valor_multa', $conta->valor_multa) }}" step="0.01" min="0"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor
                                Juros</label>
                            <input type="number" name="valor_juros"
                                value="{{ old('valor_juros', $conta->valor_juros) }}" step="0.01" min="0"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data Emissão
                                *</label>
                            <input type="date" name="data_emissao"
                                value="{{ old('data_emissao', $conta->data_emissao->format('Y-m-d')) }}" required
                                {{ in_array($conta->status, ['recebido', 'cancelado']) ? 'readonly' : '' }}
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('data_emissao')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data
                                Vencimento *</label>
                            <input type="date" name="data_vencimento"
                                value="{{ old('data_vencimento', $conta->data_vencimento->format('Y-m-d')) }}" required
                                {{ in_array($conta->status, ['recebido', 'cancelado']) ? 'readonly' : '' }}
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('data_vencimento')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select name="status"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                {{ $conta->status === 'recebido' ? 'disabled' : '' }}>
                                <option value="pendente"
                                    {{ old('status', $conta->status) === 'pendente' ? 'selected' : '' }}>Pendente
                                </option>
                                <option value="enviado"
                                    {{ old('status', $conta->status) === 'enviado' ? 'selected' : '' }}>Enviado
                                </option>
                                <option value="a_vencer"
                                    {{ old('status', $conta->status) === 'a_vencer' ? 'selected' : '' }}>A Vencer
                                </option>
                                <option value="vencido"
                                    {{ old('status', $conta->status) === 'vencido' ? 'selected' : '' }}>Vencido
                                </option>
                                <option value="recebido"
                                    {{ old('status', $conta->status) === 'recebido' ? 'selected' : '' }}>Recebido
                                </option>
                                <option value="cancelado"
                                    {{ old('status', $conta->status) === 'cancelado' ? 'selected' : '' }}>Cancelado
                                </option>
                                <option value="em_cobranca"
                                    {{ old('status', $conta->status) === 'em_cobranca' ? 'selected' : '' }}>Em Cobrança
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prioridade</label>
                            <select name="priority"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="baixa"
                                    {{ old('priority', $conta->priority) === 'baixa' ? 'selected' : '' }}>Baixa
                                </option>
                                <option value="media"
                                    {{ old('priority', $conta->priority) === 'media' ? 'selected' : '' }}>Média
                                </option>
                                <option value="alta"
                                    {{ old('priority', $conta->priority) === 'alta' ? 'selected' : '' }}>Alta</option>
                                <option value="urgente"
                                    {{ old('priority', $conta->priority) === 'urgente' ? 'selected' : '' }}>Urgente
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Consultor</label>
                            <select name="consultant_id"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Selecione</option>
                                @foreach ($consultores ?? [] as $consultor)
                                    <option value="{{ $consultor->id }}"
                                        {{ old('consultant_id', $conta->consultant_id) == $consultor->id ? 'selected' : '' }}>
                                        {{ $consultor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comissão
                                (%)</label>
                            <input type="number" name="commission_percentage"
                                value="{{ old('commission_percentage', $conta->commission_percentage) }}"
                                step="0.01" min="0" max="100"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Centro de
                                Custo</label>
                            <input type="text" name="centro_custo"
                                value="{{ old('centro_custo', $conta->centro_custo) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoria</label>
                            <input type="text" name="categoria" value="{{ old('categoria', $conta->categoria) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição
                                *</label>
                            <textarea name="descricao" rows="2" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('descricao', $conta->descricao) }}</textarea>
                            @error('descricao')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observações</label>
                            <textarea name="observacoes" rows="3"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('observacoes', $conta->observacoes) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Dados de Recebimento (se já recebido) --}}
                @if ($conta->status === 'recebido')
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dados do Recebimento
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor
                                    Recebido</label>
                                <input type="text"
                                    value="R$ {{ number_format($conta->valor_recebido, 2, ',', '.') }}" readonly
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-300 dark:text-gray-700 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data
                                    Recebimento</label>
                                <input type="text" value="{{ $conta->data_recebimento?->format('d/m/Y') }}"
                                    readonly
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-300 dark:text-gray-700 shadow-sm">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Submit --}}
                <div class="flex justify-between items-center">
                    <button type="button" onclick="confirmDelete()"
                        class="px-6 py-3 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium transition duration-150">
                        <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Excluir Conta
                    </button>
                    <div class="flex space-x-4">
                        <a href="{{ route('financeiro.contas-receber.show', $conta) }}"
                            class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 font-medium">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                            Atualizar Conta
                        </button>
                    </div>
                </div>
            </form>

            {{-- Formulário oculto para exclusão --}}
            <form id="delete-form" action="{{ route('financeiro.contas-receber.destroy', $conta) }}" method="POST"
                class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmDelete() {
                if (confirm('Tem certeza que deseja excluir esta conta a receber?\n\nEsta ação não pode ser desfeita.')) {
                    document.getElementById('delete-form').submit();
                }
            }
        </script>
    @endpush
</x-app-layout>
