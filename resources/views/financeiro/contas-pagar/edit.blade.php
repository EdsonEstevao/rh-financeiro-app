{{-- resources/views/financeiro/contas-pagar/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Editar Conta a Pagar') }} #{{ $conta->numero_documento }}
            </h2>
            <a href="{{ route('financeiro.contas-pagar.show', $conta) }}"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium">
                ← Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('financeiro.contas-pagar.update', $conta) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Status Alert --}}
                @if (in_array($conta->status, ['pago', 'cancelado']))
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
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm"
                                {{ in_array($conta->status, ['pago', 'cancelado']) ? 'disabled' : '' }}>
                                @foreach ($tipos as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('tipo', $conta->tipo) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fornecedor</label>
                            <select name="fornecedor_id"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm"
                                {{ in_array($conta->status, ['pago', 'cancelado']) ? 'disabled' : '' }}>
                                <option value="">Selecione</option>
                                @foreach ($fornecedores as $fornecedor)
                                    <option value="{{ $fornecedor->id }}"
                                        {{ old('fornecedor_id', $conta->fornecedor_id) == $fornecedor->id ? 'selected' : '' }}>
                                        {{ $fornecedor->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Beneficiário
                                *</label>
                            <input type="text" name="beneficiario_nome"
                                value="{{ old('beneficiario_nome', $conta->beneficiario_nome) }}" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CPF/CNPJ</label>
                            <input type="text" name="beneficiario_documento"
                                value="{{ old('beneficiario_documento', $conta->beneficiario_documento) }}"
                                x-mask="999.999.999-99"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email
                                Beneficiário</label>
                            <input type="email" name="beneficiario_email"
                                value="{{ old('beneficiario_email', $conta->beneficiario_email) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor (R$)
                                *</label>
                            <input type="number" name="valor_original"
                                value="{{ old('valor_original', $conta->valor_original) }}" step="0.01"
                                min="0.01" required
                                {{ in_array($conta->status, ['pago', 'cancelado']) ? 'readonly' : '' }}
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor
                                Desconto</label>
                            <input type="number" name="valor_desconto"
                                value="{{ old('valor_desconto', $conta->valor_desconto) }}" step="0.01"
                                min="0"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor
                                Multa</label>
                            <input type="number" name="valor_multa"
                                value="{{ old('valor_multa', $conta->valor_multa) }}" step="0.01" min="0"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor
                                Juros</label>
                            <input type="number" name="valor_juros"
                                value="{{ old('valor_juros', $conta->valor_juros) }}" step="0.01" min="0"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data Emissão
                                *</label>
                            <input type="date" name="data_emissao"
                                value="{{ old('data_emissao', $conta->data_emissao->format('Y-m-d')) }}" required
                                {{ in_array($conta->status, ['pago', 'cancelado']) ? 'readonly' : '' }}
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data
                                Vencimento *</label>
                            <input type="date" name="data_vencimento"
                                value="{{ old('data_vencimento', $conta->data_vencimento->format('Y-m-d')) }}" required
                                {{ in_array($conta->status, ['pago', 'cancelado']) ? 'readonly' : '' }}
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select name="status"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm"
                                {{ $conta->status === 'pago' ? 'disabled' : '' }}>
                                <option value="pendente"
                                    {{ old('status', $conta->status) === 'pendente' ? 'selected' : '' }}>Pendente
                                </option>
                                <option value="aprovado"
                                    {{ old('status', $conta->status) === 'aprovado' ? 'selected' : '' }}>Aprovado
                                </option>
                                <option value="agendado"
                                    {{ old('status', $conta->status) === 'agendado' ? 'selected' : '' }}>Agendado
                                </option>
                                <option value="pago"
                                    {{ old('status', $conta->status) === 'pago' ? 'selected' : '' }}>Pago</option>
                                <option value="cancelado"
                                    {{ old('status', $conta->status) === 'cancelado' ? 'selected' : '' }}>Cancelado
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prioridade</label>
                            <select name="priority"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
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
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Centro de
                                Custo</label>
                            <input type="text" name="centro_custo"
                                value="{{ old('centro_custo', $conta->centro_custo) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoria</label>
                            <input type="text" name="categoria" value="{{ old('categoria', $conta->categoria) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição
                                *</label>
                            <textarea name="descricao" rows="2" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">{{ old('descricao', $conta->descricao) }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observações</label>
                            <textarea name="observacoes" rows="3"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">{{ old('observacoes', $conta->observacoes) }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instruções
                                de Pagamento</label>
                            <textarea name="instrucoes_pagamento" rows="2"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">{{ old('instrucoes_pagamento', $conta->instrucoes_pagamento) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Dados Bancários --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dados Bancários (para
                        pagamento)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Código de
                                Barras</label>
                            <input type="text" name="codigo_barras"
                                value="{{ old('codigo_barras', $conta->codigo_barras) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Linha
                                Digitável</label>
                            <input type="text" name="linha_digitavel"
                                value="{{ old('linha_digitavel', $conta->linha_digitavel) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Chave
                                PIX</label>
                            <input type="text" name="pix_chave" value="{{ old('pix_chave', $conta->pix_chave) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                    </div>
                </div>

                {{-- Dados de Pagamento (se já pago) --}}
                @if ($conta->status === 'pago')
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dados do Pagamento</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor
                                    Pago</label>
                                <input type="text" value="R$ {{ number_format($conta->valor_pago, 2, ',', '.') }}"
                                    readonly
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-300 dark:text-gray-700 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data
                                    Pagamento</label>
                                <input type="text" value="{{ $conta->data_pagamento?->format('d/m/Y') }}" readonly
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
                        <a href="{{ route('financeiro.contas-pagar.show', $conta) }}"
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
            <form id="delete-form" action="{{ route('financeiro.contas-pagar.destroy', $conta) }}" method="POST"
                class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmDelete() {
                if (confirm('Tem certeza que deseja excluir esta conta a pagar?\n\nEsta ação não pode ser desfeita.')) {
                    document.getElementById('delete-form').submit();
                }
            }
        </script>
    @endpush
</x-app-layout>
