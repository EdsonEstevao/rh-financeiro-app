{{-- resources/views/rh/employees/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $employee->user->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('rh.employees.edit', $employee) }}"
                    class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 text-sm">Editar</a>
                <a href="{{ route('rh.employees.index') }}"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm">Voltar</a>
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <div class="flex flex-col items-center">
                        <div
                            class="h-24 w-24 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-3xl mb-4">
                            {{ strtoupper(substr($employee->user->name, 0, 2)) }}</div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $employee->user->name }}
                        </h3>
                        <p class="text-sm text-gray-500">{{ $employee->position }}</p>
                        <span
                            class="mt-2 px-3 py-1 text-xs font-semibold rounded-full @if ($employee->status === 'active') bg-green-100 text-green-800 @elseif($employee->status === 'vacation') bg-blue-100 text-blue-800 @else bg-red-100 text-red-800 @endif">{{ ucfirst($employee->status) }}</span>
                    </div>
                </div>
                <div class="md:col-span-2 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Informações</h3>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $employee->user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">CPF</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $employee->user->cpf ?? '---' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Departamento</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $employee->department?->name ?? '---' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Salário</dt>
                            <dd class="font-medium text-green-600">R$
                                {{ number_format($employee->salary, 2, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Admissão</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $employee->hire_date->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Tipo Contrato</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">
                                {{ strtoupper($employee->employment_type) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
