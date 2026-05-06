<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Novo Fornecedor') }}
            </h2>
            <a href="{{ route('financeiro.fornecedores.index') }}"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium">
                ← Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('financeiro.fornecedores.store') }}" method="POST" x-data="fornecedorForm()"
                class="space-y-6">
                @csrf

                {{-- Dados Básicos --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dados Básicos</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome/Razão
                                Social *</label>
                            <input type="text" name="nome" x-model="form.nome" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome
                                Fantasia</label>
                            <input type="text" name="nome_fantasia" x-model="form.nome_fantasia"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CPF/CNPJ
                                *</label>
                            <input type="text" name="documento" x-model="form.documento" x-mask="999.999.999-99"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo Pessoa
                                *</label>
                            <select name="tipo_pessoa" x-model="form.tipo_pessoa" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="juridica">Pessoa Jurídica</option>
                                <option value="fisica">Pessoa Física</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoria</label>
                            <select name="categoria" x-model="form.categoria"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">Selecione</option>
                                <option value="servicos">Serviços</option>
                                <option value="produtos">Produtos</option>
                                <option value="tecnologia">Tecnologia</option>
                                <option value="consultoria">Consultoria</option>
                                <option value="alimentacao">Alimentação</option>
                                <option value="transporte">Transporte</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Contato --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Contato</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email" name="email" x-model="form.email"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telefone</label>
                            <input type="text" name="telefone" x-model="form.telefone" x-mask="(99) 9999-9999"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Celular</label>
                            <input type="text" name="celular" x-model="form.celular" x-mask="(99) 99999-9999"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                    </div>
                </div>

                {{-- Dados Bancários --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dados Bancários</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Banco</label>
                            <input type="text" name="banco_nome" x-model="form.banco_nome"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Agência</label>
                            <input type="text" name="agencia" x-model="form.agencia"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Conta</label>
                            <input type="text" name="conta" x-model="form.conta"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Chave
                                PIX</label>
                            <input type="text" name="pix_chave" x-model="form.pix_chave"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                    </div>
                </div>

                {{-- Observações --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Observações</h3>
                    <textarea name="observacoes" rows="3"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm"></textarea>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('financeiro.fornecedores.index') }}"
                        class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 font-medium">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                        Salvar Fornecedor
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function fornecedorForm() {
                return {
                    form: {
                        nome: '',
                        nome_fantasia: '',
                        documento: '',
                        tipo_pessoa: 'juridica',
                        categoria: '',
                        email: '',
                        telefone: '',
                        celular: '',
                        banco_nome: '',
                        agencia: '',
                        conta: '',
                        pix_chave: '',
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
