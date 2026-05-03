{{-- resources/views/rh/reports/terminations.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Relatório de Desligamentos') }}</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @php $terminated = \App\Models\Employee::where('status','terminated')->get(); @endphp
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Total Desligados</p>
                    <p class="text-2xl font-bold text-red-600">{{ $terminated->count() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Este Ano</p>
                    <p class="text-2xl font-bold text-yellow-600">
                        {{ $terminated->where('termination_date', '>=', now()->startOfYear())->count() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Este Mês</p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ $terminated->where('termination_date', '>=', now()->startOfMonth())->count() }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Funcionário</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cargo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admissão</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Desligamento
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($terminated as $emp)
                            <tr>
                                <td class="px-6 py-4 text-sm">{{ $emp->user->name }}</td>
                                <td class="px-6 py-4 text-sm">{{ $emp->position }}</td>
                                <td class="px-6 py-4 text-sm">{{ $emp->hire_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-sm">{{ $emp->termination_date?->format('d/m/Y') ?? '---' }}
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $emp->termination_type ?? '---' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
