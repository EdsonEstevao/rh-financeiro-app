{{-- resources/views/components/boleto-form.blade.php --}}
@props(['boleto' => null, 'users' => []])

<div x-data="boletoForm()" x-init="init({{ $boleto ? json_encode($boleto) : 'null' }})" class="space-y-6">

    <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Dados do Pagador -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Dados do Pagador</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cliente</label>
                    <select x-model="form.user_id" @change="loadUserData($event.target.value)"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Selecione o cliente</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->cpf }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome do Pagador</label>
                    <input type="text" x-model="form.payer_name"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        :readonly="form.user_id">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">CPF/CNPJ do
                        Pagador</label>
                    <input type="text" x-model="form.payer_document" x-mask="999.999.999-99"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        :readonly="form.user_id">
                </div>
            </div>
        </div>

        <!-- Dados do Boleto -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Dados do Boleto</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Valor (R$)</label>
                    <input type="text" x-model="form.amount" x-mask:dynamic="$money($input)"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Vencimento</label>
                    <input type="date" x-model="form.due_date"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nosso Número</label>
                    <input type="text" x-model="form.our_number" :value="generateOurNumber()" readonly
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-300 dark:text-gray-700 shadow-sm">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição</label>
                <textarea x-model="form.description" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Descrição do boleto..."></textarea>
            </div>

            <!-- Multas e Juros -->
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Multa (%)</label>
                    <input type="number" x-model="form.fine_percentage" step="0.01" value="2.00"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Juros ao Mês (%)</label>
                    <input type="number" x-model="form.interest_percentage" step="0.01" value="1.00"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Desconto (R$)</label>
                    <input type="text" x-model="form.discount_amount" x-mask:dynamic="$money($input)" value="0,00"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Preview do Boleto -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Preview do Boleto</h3>

            <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Beneficiário</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ config('app.name') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Vencimento</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100" x-text="formatDate(form.due_date)">
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-between items-start">
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Pagador</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100" x-text="form.payer_name || '---'">
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400" x-text="form.payer_document || '---'">
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Valor</div>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400"
                            x-text="formatCurrency(form.amount)"></div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Linha Digitável</div>
                    <div class="font-mono text-sm text-gray-900 dark:text-gray-100" x-text="generateBarcode()"></div>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex justify-end space-x-4">
            <button type="button" @click="window.history.back()"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
            </button>

            <button type="button" @click="generatePreview()"
                class="px-4 py-2 border border-blue-300 dark:border-blue-600 rounded-md shadow-sm text-sm font-medium text-blue-700 dark:text-blue-300 bg-white dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Visualizar
            </button>

            <button type="submit"
                class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                :disabled="isSubmitting">
                <span x-show="!isSubmitting">Gerar Boleto</span>
                <span x-show="isSubmitting" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Gerando...
                </span>
            </button>
        </div>
    </form>

    <!-- Modal de Preview -->
    <div x-show="showPreview" x-transition
        class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
        @click.self="showPreview = false">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Boleto Bancário</h3>
                    <button @click="showPreview = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <!-- Conteúdo do boleto -->
                <div id="boleto-content" class="border-2 border-gray-300 dark:border-gray-600 p-6 rounded-lg">
                    <!-- Layout do boleto aqui -->
                </div>
                <div class="mt-4 flex justify-end space-x-4">
                    <button @click="printBoleto()"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Imprimir
                    </button>
                    <button @click="downloadPDF()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function boletoForm() {
        return {
            isSubmitting: false,
            showPreview: false,
            form: {
                user_id: '',
                payer_name: '',
                payer_document: '',
                amount: '',
                due_date: '',
                description: '',
                our_number: '',
                fine_percentage: 2.00,
                interest_percentage: 1.00,
                discount_amount: 0,
            },

            init(data) {
                if (data) {
                    this.form = {
                        ...this.form,
                        ...data
                    };
                }
                this.form.our_number = this.generateOurNumber();
            },

            generateOurNumber() {
                const date = new Date();
                const random = Math.floor(Math.random() * 1000000).toString().padStart(6, '0');
                return `${date.getFullYear()}${(date.getMonth() + 1).toString().padStart(2, '0')}${random}`;
            },

            generateBarcode() {
                // Simulação de geração de linha digitável
                const segments = [
                    '34191', '79001', '01000',
                    this.form.our_number || '000000',
                    '00571', '23456', '78901'
                ];
                return segments.join(' ');
            },

            formatDate(date) {
                if (!date) return '---';
                return new Date(date).toLocaleDateString('pt-BR');
            },

            formatCurrency(value) {
                if (!value) return 'R$ 0,00';
                return new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                }).format(parseFloat(value.toString().replace(',', '.')));
            },

            async loadUserData(userId) {
                if (!userId) return;

                try {
                    const response = await fetch(`/api/users/${userId}`);
                    const user = await response.json();

                    this.form.payer_name = user.name;
                    this.form.payer_document = user.cpf;

                } catch (error) {
                    console.error('Error loading user:', error);
                }
            },

            generatePreview() {
                this.showPreview = true;
            },

            printBoleto() {
                window.print();
            },

            async downloadPDF() {
                // Implementar download do PDF
                alert('Funcionalidade de download será implementada');
            },

            async submitForm() {
                this.isSubmitting = true;

                try {
                    const response = await fetch('{{ route('financeiro.boletos.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Erro ao gerar boleto');
                    }

                    const result = await response.json();

                    // Redirecionar para o boleto gerado
                    window.location.href = `/financeiro/boletos/${result.id}`;

                } catch (error) {
                    console.error('Error:', error);
                    alert(error.message || 'Erro ao gerar boleto');
                } finally {
                    this.isSubmitting = false;
                }
            }
        }
    }
</script>
