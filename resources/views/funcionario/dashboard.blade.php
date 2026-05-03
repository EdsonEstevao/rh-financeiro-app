{{-- resources/views/funcionario/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Meu Painel') }}
            </h2>
            <div class="flex items-center space-x-3">
                <span
                    class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    {{ auth()->user()->employee?->status === 'active' ? 'Ativo' : auth()->user()->employee?->status ?? 'Não vinculado' }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ auth()->user()->employee?->position ?? 'Funcionário' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Welcome Card --}}
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 overflow-hidden shadow-sm rounded-lg mb-8">
                <div class="p-6 sm:p-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-white">
                                Olá, {{ auth()->user()->name }}!
                            </h3>
                            <p class="mt-2 text-blue-100">
                                {{ now()->format('d') }} de {{ now()->translatedFormat('F') }} de
                                {{ now()->format('Y') }}
                            </p>
                            @if (auth()->user()->employee)
                                <p class="mt-1 text-blue-200 text-sm">
                                    {{ auth()->user()->employee->department?->name }} •
                                    {{ auth()->user()->employee->hire_date->diffForHumans(null, true) }} de empresa
                                </p>
                            @endif
                        </div>
                        <div class="hidden sm:block">
                            <div
                                class="h-20 w-20 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-3xl">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Acesso Rápido</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('funcionario.payroll') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700 group">
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900 group-hover:scale-110 transition">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">Holerite</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Consultar pagamentos</span>
                    </a>

                    <a href="{{ route('funcionario.boletos') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700 group">
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 group-hover:scale-110 transition">
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">Meus Boletos</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Visualizar e baixar</span>
                    </a>

                    <a href="{{ route('profile.edit') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700 group">
                        <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900 group-hover:scale-110 transition">
                            <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <span class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">Meu Perfil</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Atualizar dados</span>
                    </a>

                    @if (auth()->user()->employee)
                        <a href="{{ route('rh.documents.index') }}"
                            class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700 group">
                            <div
                                class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900 group-hover:scale-110 transition">
                                <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <span class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">Documentos</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Meus documentos</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Info Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @if (auth()->user()->employee)
                    {{-- Employment Info Card --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Dados Profissionais
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-xs text-gray-500 dark:text-gray-400">Cargo</label>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ auth()->user()->employee->position }}</p>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 dark:text-gray-400">Departamento</label>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ auth()->user()->employee->department?->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 dark:text-gray-400">Data de Admissão</label>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ auth()->user()->employee->hire_date->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 dark:text-gray-400">Tipo de Contrato</label>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ auth()->user()->employee->employment_type === 'clt' ? 'CLT' : strtoupper(auth()->user()->employee->employment_type) }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 dark:text-gray-400">Carga Horária</label>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ auth()->user()->employee->workload_hours }}h semanais</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Salary Card --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Remuneração
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-xs text-gray-500 dark:text-gray-400">Salário Base</label>
                                    <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                        R$ {{ number_format(auth()->user()->employee->salary, 2, ',', '.') }}
                                    </p>
                                </div>
                                @if (auth()->user()->employee->benefits_value)
                                    <div>
                                        <label class="text-xs text-gray-500 dark:text-gray-400">Benefícios</label>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            R$
                                            {{ number_format(auth()->user()->employee->benefits_value, 2, ',', '.') }}
                                        </p>
                                    </div>
                                @endif
                                <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <label class="text-xs text-gray-500 dark:text-gray-400">Total Remuneração</label>
                                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                        R$
                                        {{ number_format(auth()->user()->employee->salary + (auth()->user()->employee->benefits_value ?? 0), 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Benefits Card --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Benefícios
                            </h3>
                            <div class="space-y-2">
                                @php $employee = auth()->user()->employee; @endphp

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Plano de Saúde</span>
                                    <span
                                        class="{{ $employee->has_health_plan ? 'text-green-500' : 'text-red-500' }}">
                                        @if ($employee->has_health_plan)
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Plano Odontológico</span>
                                    <span
                                        class="{{ $employee->has_dental_plan ? 'text-green-500' : 'text-red-500' }}">
                                        @if ($employee->has_dental_plan)
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Vale Refeição</span>
                                    <span
                                        class="{{ $employee->has_meal_voucher ? 'text-green-500' : 'text-red-500' }}">
                                        @if ($employee->has_meal_voucher)
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Vale Alimentação</span>
                                    <span
                                        class="{{ $employee->has_food_voucher ? 'text-green-500' : 'text-red-500' }}">
                                        @if ($employee->has_food_voucher)
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Vale Transporte</span>
                                    <span
                                        class="{{ $employee->has_transportation_voucher ? 'text-green-500' : 'text-red-500' }}">
                                        @if ($employee->has_transportation_voucher)
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Recent Payroll & Boletos --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Recent Payroll --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Últimos Holerites</h3>
                            <a href="{{ route('funcionario.payroll') }}"
                                class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Ver todos →</a>
                        </div>

                        @php
                            $payrolls =
                                $payroll_history ??
                                \App\Models\Payroll::where('employee_id', auth()->user()->employee?->id)
                                    ->latest()
                                    ->take(5)
                                    ->get();
                        @endphp

                        @if ($payrolls && $payrolls->count() > 0)
                            <div class="space-y-3">
                                @foreach ($payrolls as $payroll)
                                    <div
                                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0 p-2 rounded-full bg-green-100 dark:bg-green-900">
                                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ \Carbon\Carbon::createFromDate($payroll->year, $payroll->month, 1)->translatedFormat('F/Y') }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ ucfirst($payroll->type) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                R$ {{ number_format($payroll->net_salary, 2, ',', '.') }}
                                            </p>
                                            <a href="{{ route('funcionario.payroll.show', $payroll) }}"
                                                class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                                Visualizar
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nenhum holerite disponível</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Recent Boletos --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Meus Boletos</h3>
                            <a href="{{ route('funcionario.boletos') }}"
                                class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Ver todos →</a>
                        </div>

                        @php
                            $boletos = $boletos ?? auth()->user()->boletos()->latest()->take(5)->get();
                        @endphp

                        @if ($boletos && $boletos->count() > 0)
                            <div class="space-y-3">
                                @foreach ($boletos as $boleto)
                                    <div
                                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="flex-shrink-0 p-2 rounded-full
                                                @if ($boleto->status === 'paid') bg-green-100 dark:bg-green-900
                                                @elseif($boleto->status === 'overdue') bg-red-100 dark:bg-red-900
                                                @else bg-yellow-100 dark:bg-yellow-900 @endif">
                                                <svg class="w-4 h-4
                                                    @if ($boleto->status === 'paid') text-green-600 dark:text-green-400
                                                    @elseif($boleto->status === 'overdue') text-red-600 dark:text-red-400
                                                    @else text-yellow-600 dark:text-yellow-400 @endif"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ Str::limit($boleto->description, 30) }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    Venc: {{ $boleto->due_date->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                R$ {{ number_format($boleto->amount, 2, ',', '.') }}
                                            </p>
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full
                                                @if ($boleto->status === 'paid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @elseif($boleto->status === 'overdue') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif">
                                                @if ($boleto->status === 'paid')
                                                    Pago
                                                @elseif($boleto->status === 'overdue')
                                                    Vencido
                                                @else
                                                    Pendente
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nenhum boleto disponível</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
