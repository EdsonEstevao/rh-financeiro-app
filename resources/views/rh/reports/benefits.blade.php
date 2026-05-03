{{-- resources/views/rh/reports/benefits.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Relatório de Benefícios') }}</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @php $emps = \App\Models\Employee::where('status','active')->get(); @endphp
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Plano de Saúde</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $emps->where('has_health_plan', true)->count() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Vale Refeição</p>
                    <p class="text-2xl font-bold text-green-600">{{ $emps->where('has_meal_voucher', true)->count() }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Vale Transporte</p>
                    <p class="text-2xl font-bold text-yellow-600">
                        {{ $emps->where('has_transportation_voucher', true)->count() }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Funcionário</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Saúde</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Odontológico
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">VR</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">VA</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">VT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($emps as $emp)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 text-sm">{{ $emp->user->name }}</td>
                                @foreach (['has_health_plan', 'has_dental_plan', 'has_meal_voucher', 'has_food_voucher', 'has_transportation_voucher'] as $benefit)
                                    <td class="px-6 py-4 text-center">{{ $emp->$benefit ? '✓' : '✗' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
