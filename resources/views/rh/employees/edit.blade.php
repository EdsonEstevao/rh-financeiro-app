{{-- resources/views/rh/employees/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar: ') }}{{ $employee->user->name }}</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('rh.employees.update', $employee) }}" method="POST" class="space-y-6">
                @csrf @method('PUT')
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dados Pessoais</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2"><label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome
                                *</label><input type="text" name="name"
                                value="{{ old('name', $employee->user->name) }}" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email
                                *</label><input type="email" name="email"
                                value="{{ old('email', $employee->user->email) }}" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div><label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CPF</label><input
                                type="text" name="cpf" value="{{ old('cpf', $employee->user->cpf) }}"
                                x-mask="999.999.999-99"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dados Profissionais</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cargo
                                *</label><input type="text" name="position"
                                value="{{ old('position', $employee->position) }}" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Departamento
                                *</label><select name="department_id" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                @foreach (\App\Models\Department::all() as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ $employee->department_id == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}</option>
                                @endforeach
                            </select></div>
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Salário
                                *</label><input type="number" name="salary"
                                value="{{ old('salary', $employee->salary) }}" step="0.01" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div><label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label><select
                                name="status"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="active" {{ $employee->status === 'active' ? 'selected' : '' }}>Ativo
                                </option>
                                <option value="vacation" {{ $employee->status === 'vacation' ? 'selected' : '' }}>
                                    Férias</option>
                                <option value="terminated" {{ $employee->status === 'terminated' ? 'selected' : '' }}>
                                    Desligado</option>
                            </select></div>
                    </div>
                </div>
                <div class="flex justify-end space-x-4">
                    <form action="{{ route('rh.employees.destroy', $employee) }}" method="POST"
                        onsubmit="return confirm('Excluir este funcionário?')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="px-6 py-3 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium">Excluir</button>
                    </form>
                    <a href="{{ route('rh.employees.index') }}"
                        class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 font-medium">Cancelar</a>
                    <button type="submit"
                        class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">Atualizar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
