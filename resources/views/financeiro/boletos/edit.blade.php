{{-- resources/views/financeiro/boletos/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Editar Boleto #') }}{{ $boleto->boleto_number }}
            </h2>
            <a href="{{ route('financeiro.boletos.show', $boleto) }}"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium">
                ← Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('financeiro.boletos.update', $boleto) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Mesmo layout do create, mas com valores preenchidos --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dados do Pagador</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome do
                                    Pagador *</label>
                                <input type="text" name="payer_name"
                                    value="{{ old('payer_name', $boleto->payer_name) }}" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CPF/CNPJ
                                    *</label>
                                <input type="text" name="payer_document"
                                    value="{{ old('payer_document', $boleto->payer_document) }}" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dados do Boleto</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor
                                    (R$) *</label>
                                <input type="number" name="amount" value="{{ old('amount', $boleto->amount) }}"
                                    step="0.01" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vencimento
                                    *</label>
                                <input type="date" name="due_date"
                                    value="{{ old('due_date', $boleto->due_date->format('Y-m-d')) }}" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            @php
                                use App\Enums\BoletoStatus;
                            @endphp
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                {{-- <select name="status"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm"
                                    @if (in_array($boleto->status, ['paid', 'cancelled'])) disabled @endif>
                                    <option value="pending" {{ $boleto->status === 'pending' ? 'selected' : '' }}>
                                        Pendente</option>
                                    <option value="paid" {{ $boleto->status === 'paid' ? 'selected' : '' }}>Pago
                                    </option>
                                    <option value="cancelled" {{ $boleto->status === 'cancelled' ? 'selected' : '' }}>
                                        Cancelado</option>
                                </select> --}}
                                <select name="status">
                                    @foreach (BoletoStatus::toSelectArray() as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ $boleto->status->value === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <select name="payment_method">
                                    @foreach (PaymentMethod::groupedForSelect() as $group => $methods)
                                        <optgroup label="{{ $group }}">
                                            @foreach ($methods as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição
                                    *</label>
                                <textarea name="description" rows="2" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $boleto->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('financeiro.boletos.show', $boleto) }}"
                        class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 font-medium">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                        Atualizar Boleto
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
