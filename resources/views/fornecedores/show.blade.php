<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $fornecedor->nome }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('financeiro.fornecedores.edit', $fornecedor) }}"
                    class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 text-sm">Editar</a>
                <a href="{{ route('financeiro.fornecedores.index') }}"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm">Voltar</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Info Card --}}
                <div class="lg:col-span-1 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <div class="text-center">
                        <div
                            class="h-20 w-20 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-2xl mx-auto mb-4">
                            {{ strtoupper(substr($fornecedor->nome, 0, 2)) }}
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $fornecedor->nome }}</h3>
                        <p class="text-sm text-gray-500">{{ $fornecedor->nome_fantasia }}</p>
                        <span
                            class="mt-2 px-3 py-1 text-xs font-semibold rounded-full {{ $fornecedor->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $fornecedor->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>
                    <div class="mt-6 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Documento:</span>
                            <span
                                class="font-medium">{{ $fornecedor->documento_formatted ?? $fornecedor->documento }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tipo:</span>
                            <span class="font-medium">{{ $fornecedor->tipo_pessoa === 'juridica' ? 'PJ' : 'PF' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Categoria:</span>
                            <span class="font-medium">{{ $fornecedor->categoria ?? 'Geral' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Details --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Contact Info --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Contato</h3>
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500">Email</dt>
                                <dd class="font-medium">{{ $fornecedor->email ?? '---' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Telefone</dt>
                                <dd class="font-medium">{{ $fornecedor->telefone ?? '---' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Celular</dt>
                                <dd class="font-medium">{{ $fornecedor->celular ?? '---' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Bank Info --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dados Bancários</h3>
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500">Banco</dt>
                                <dd class="font-medium">{{ $fornecedor->banco_nome ?? '---' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Agência</dt>
                                <dd class="font-medium">{{ $fornecedor->agencia ?? '---' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Conta</dt>
                                <dd class="font-medium">{{ $fornecedor->conta ?? '---' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Chave PIX</dt>
                                <dd class="font-medium">{{ $fornecedor->pix_chave ?? '---' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Contas a Pagar --}}
                    @if ($fornecedor->contasPagar && $fornecedor->contasPagar->count() > 0)
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Contas a Pagar
                                Recentes</h3>
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500">
                                        <th class="pb-2">Documento</th>
                                        <th class="pb-2">Valor</th>
                                        <th class="pb-2">Vencimento</th>
                                        <th class="pb-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fornecedor->contasPagar->take(5) as $conta)
                                        <tr class="border-t dark:border-gray-700">
                                            <td class="py-2">{{ $conta->numero_documento }}</td>
                                            <td class="py-2">R$ {{ number_format($conta->valor_total, 2, ',', '.') }}
                                            </td>
                                            <td class="py-2">{{ $conta->data_vencimento->format('d/m/Y') }}</td>
                                            <td class="py-2">
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full {{ $conta->status === 'pago' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ $conta->status_label }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
