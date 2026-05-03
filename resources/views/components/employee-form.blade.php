<!-- resources/views/components/employee-form.blade.php -->
<div x-data="employeeForm()" class="max-w-4xl mx-auto p-6">
    <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Seção Dados Pessoais -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Dados Pessoais</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome Completo</label>
                    <input type="text" x-model="form.name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">CPF</label>
                    <input type="text" x-model="form.cpf" x-mask="999.999.999-99"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required>
                </div>
            </div>
        </div>

        <!-- Seção Documentos -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Documentos</h3>

            <div x-data="{ documents: [] }" class="space-y-4">
                <template x-for="(doc, index) in documents" :key="index">
                    <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <select x-model="doc.type" class="block w-full rounded-md border-gray-300">
                                <option value="">Selecione o tipo</option>
                                <option value="rg">RG</option>
                                <option value="ctps">CTPS</option>
                                <option value="reservist">Reservista</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <input type="file"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                        <button type="button" @click="documents.splice(index, 1)"
                            class="text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                        </button>
                    </div>
                </template>

                <button type="button" @click="documents.push({ type: '', file: null })"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200">
                    Adicionar Documento
                </button>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex justify-end space-x-4">
            <button type="button" @click="resetForm()"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cancelar
            </button>
            <button type="submit"
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                :disabled="isSubmitting">
                <span x-show="!isSubmitting">Salvar</span>
                <span x-show="isSubmitting" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Salvando...
                </span>
            </button>
        </div>
    </form>
</div>

<script>
    function employeeForm() {
        return {
            isSubmitting: false,
            form: {
                name: '',
                cpf: '',
                email: '',
                phone: '',
                salary: '',
                department_id: '',
                position: '',
            },

            async submitForm() {
                this.isSubmitting = true;

                try {
                    const response = await fetch('/api/rh/employees', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.form)
                    });

                    if (!response.ok) {
                        throw new Error('Erro ao salvar');
                    }

                    const data = await response.json();

                    // Redirecionar ou mostrar mensagem de sucesso
                    window.location.href = `/rh/employees/${data.id}`;

                } catch (error) {
                    console.error('Error:', error);
                    // Mostrar mensagem de erro
                    alert('Erro ao salvar funcionário');
                } finally {
                    this.isSubmitting = false;
                }
            },

            resetForm() {
                this.form = {
                    name: '',
                    cpf: '',
                    email: '',
                    phone: '',
                    salary: '',
                    department_id: '',
                    position: '',
                };
            }
        }
    }
</script>
