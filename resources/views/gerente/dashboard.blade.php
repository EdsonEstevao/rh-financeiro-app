{{-- resources/views/gerente/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard do Gerente') }}
            </h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Departamento: {{ auth()->user()->employee?->department?->name ?? 'Não atribuído' }}
                </span>
                <span
                    class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    {{ auth()->user()->employee?->position ?? 'Gerente' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Quick Actions --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ações Rápidas</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <a href="{{ route('gerente.team') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mb-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Minha
                            Equipe</span>
                    </a>

                    <a href="{{ route('rh.employees.index', ['department' => auth()->user()->employee?->department_id]) }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400 mb-2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span
                            class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Funcionários</span>
                    </a>

                    <a href="{{ route('rh.reports.payroll') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400 mb-2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Folha
                            Pagamento</span>
                    </a>

                    <a href="{{ route('rh.reports.employees') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400 mb-2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Relatórios
                            RH</span>
                    </a>

                    <a href="{{ route('financeiro.reports.index') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-pink-600 dark:text-pink-400 mb-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Rel.
                            Financeiros</span>
                    </a>

                    <a href="{{ route('financeiro.boletos.index') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mb-2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Boletos</span>
                    </a>
                </div>
            </div>

            {{-- Department Overview Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                @php
                    $department = auth()->user()->employee?->department;
                    $departmentEmployees = $department ? $department->employees : collect([]);
                    $activeEmployees = $departmentEmployees->where('status', 'active');
                    $onVacation = $departmentEmployees->where('status', 'vacation');
                    $onLeave = $departmentEmployees->whereIn('status', ['inactive', 'leave', 'suspended']);
                    $totalSalary = $activeEmployees->sum('salary');
                    $averageSalary = $activeEmployees->count() > 0 ? $totalSalary / $activeEmployees->count() : 0;
                @endphp

                {{-- Team Size Card --}}
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg transform hover:scale-105 transition duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Tamanho da Equipe</h3>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $activeEmployees->count() }}
                                    </p>
                                    <p class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                                        / {{ $departmentEmployees->count() }} total
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2 text-xs text-center">
                            <div class="bg-green-50 dark:bg-green-900/30 rounded p-2">
                                <div class="text-green-600 dark:text-green-400 font-semibold">
                                    {{ $activeEmployees->count() }}</div>
                                <div class="text-gray-500 dark:text-gray-400">Ativos</div>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-900/30 rounded p-2">
                                <div class="text-yellow-600 dark:text-yellow-400 font-semibold">
                                    {{ $onVacation->count() }}</div>
                                <div class="text-gray-500 dark:text-gray-400">Férias</div>
                            </div>
                            <div class="bg-red-50 dark:bg-red-900/30 rounded p-2">
                                <div class="text-red-600 dark:text-red-400 font-semibold">{{ $onLeave->count() }}</div>
                                <div class="text-gray-500 dark:text-gray-400">Afastados</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payroll Card --}}
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg transform hover:scale-105 transition duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-full bg-green-100 dark:bg-green-900">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Folha Salarial</h3>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        R$ {{ number_format($totalSalary, 2, ',', '.') }}
                                    </p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Média: R$ {{ number_format($averageSalary, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Performance Card --}}
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg transform hover:scale-105 transition duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Performance</h3>
                                <div class="flex items-baseline">
                                    @php
                                        $evaluatedEmployees = $activeEmployees->whereNotNull('last_evaluation_score');
                                        $avgScore =
                                            $evaluatedEmployees->count() > 0
                                                ? $evaluatedEmployees->avg('last_evaluation_score') * 2
                                                : 0;
                                        $onProbation = $activeEmployees
                                            ->filter(function ($emp) {
                                                return $emp->probation_end_date && $emp->probation_end_date->isFuture();
                                            })
                                            ->count();
                                    @endphp
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $evaluatedEmployees->count() }}
                                    </p>
                                    <p class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                                        avaliados
                                    </p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $onProbation }} em experiência
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Budget Card --}}
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg transform hover:scale-105 transition duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">

                                    Orçamento
                                </h3>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $department && $department->budget > 0 ? number_format(($totalSalary / $department->budget) * 100, 1) : 0 }}%
                                    </p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Utilizado do orçamento
                                </p>
                            </div>
                        </div>
                        @if ($department && $department->budget > 0)
                            <div class="mt-3 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-yellow-600 h-2 rounded-full"
                                    style="width: {{ min(($totalSalary / $department->budget) * 100, 100) }}%">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Team Overview & Upcoming Events --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

                {{-- Team Members List --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Minha Equipe</h3>
                            <a href="{{ route('gerente.team') }}"
                                class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                Ver todos →
                            </a>
                        </div>

                        @if ($activeEmployees->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Colaborador</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Cargo</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Admissão</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Status</th>
                                            <th
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($activeEmployees->take(5) as $employee)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-8 w-8">
                                                            <div
                                                                class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-xs">
                                                                {{ strtoupper(substr($employee->user->name ?? 'U', 0, 2)) }}
                                                            </div>
                                                        </div>
                                                        <div class="ml-3">
                                                            <div
                                                                class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                                {{ $employee->user->name ?? 'N/A' }}
                                                            </div>
                                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                                {{ $employee->user->email ?? '' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                                    {{ $employee->position }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $employee->hire_date->format('d/m/Y') }}
                                                    <div class="text-xs">
                                                        {{ $employee->hire_date->diffForHumans(null, true) }} de casa
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span
                                                        class="px-2 py-1 text-xs font-semibold rounded-full
                                                        @if ($employee->status === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                        @elseif($employee->status === 'vacation') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                        @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                        {{ $employee->status === 'active' ? 'Ativo' : ($employee->status === 'vacation' ? 'Férias' : ucfirst($employee->status)) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                                                    <a href="{{ route('gerente.team.show', $employee) }}"
                                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 mr-2">
                                                        <svg class="w-4 h-4 inline" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Nenhum membro na
                                    equipe</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sua equipe aparecerá aqui.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Right Sidebar - Upcoming Events & Alerts --}}
                <div class="space-y-8">

                    {{-- Upcoming Events --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Próximos Eventos
                            </h3>

                            <div class="space-y-3">
                                @php
                                    $upcomingVacations = $activeEmployees
                                        ->filter(function ($emp) {
                                            return $emp->vacation_start_date && $emp->vacation_start_date->isFuture();
                                        })
                                        ->sortBy('vacation_start_date')
                                        ->take(3);

                                    $birthdays = $activeEmployees
                                        ->filter(function ($emp) {
                                            return $emp->birth_date &&
                                                $emp->birth_date->format('m-d') >= now()->format('m-d') &&
                                                $emp->birth_date->format('m-d') <= now()->addDays(30)->format('m-d');
                                        })
                                        ->sortBy(function ($emp) {
                                            return $emp->birth_date->format('m-d');
                                        })
                                        ->take(3);

                                    $probationEnds = $activeEmployees
                                        ->filter(function ($emp) {
                                            return $emp->probation_end_date &&
                                                $emp->probation_end_date->isFuture() &&
                                                $emp->probation_end_date->lte(now()->addDays(30));
                                        })
                                        ->sortBy('probation_end_date')
                                        ->take(3);
                                @endphp

                                @if ($upcomingVacations->count() > 0)
                                    <div class="mb-4">
                                        <h4
                                            class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">
                                            Férias Programadas</h4>
                                        @foreach ($upcomingVacations as $emp)
                                            <div
                                                class="flex items-center space-x-3 p-2 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg mb-2">
                                                <div class="flex-shrink-0">
                                                    <svg class="w-5 h-5 text-yellow-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p
                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                        {{ $emp->user->name ?? 'N/A' }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $emp->vacation_start_date->format('d/m/Y') }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($birthdays->count() > 0)
                                    <div class="mb-4">
                                        <h4
                                            class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">
                                            Aniversariantes</h4>
                                        @foreach ($birthdays as $emp)
                                            <div
                                                class="flex items-center space-x-3 p-2 bg-pink-50 dark:bg-pink-900/30 rounded-lg mb-2">
                                                <div class="flex-shrink-0">
                                                    <svg class="w-5 h-5 text-pink-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p
                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                        {{ $emp->user->name ?? 'N/A' }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $emp->birth_date->format('d/m') }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($probationEnds->count() > 0)
                                    <div>
                                        <h4
                                            class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">
                                            Fim de Experiência</h4>
                                        @foreach ($probationEnds as $emp)
                                            <div
                                                class="flex items-center space-x-3 p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg mb-2">
                                                <div class="flex-shrink-0">
                                                    <svg class="w-5 h-5 text-blue-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p
                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                        {{ $emp->user->name ?? 'N/A' }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $emp->probation_end_date->format('d/m/Y') }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($upcomingVacations->count() === 0 && $birthdays->count() === 0 && $probationEnds->count() === 0)
                                    <div class="text-center py-4">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum evento próximo.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Quick Stats --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Resumo do Mês</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Novas contratações:</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $activeEmployees->where('hire_date', '>=', now()->startOfMonth())->count() }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Desligamentos:</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $departmentEmployees->where('status', 'terminated')->where('termination_date', '>=', now()->startOfMonth())->count() }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Documentos pendentes:</span>
                                    <span class="font-medium text-yellow-600 dark:text-yellow-400">
                                        @php
                                            $pendingDocs = \App\Models\EmployeeDocument::whereIn(
                                                'employee_id',
                                                $activeEmployees->pluck('id'),
                                            )
                                                ->where('status', 'pending')
                                                ->count();
                                        @endphp
                                        {{ $pendingDocs }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Avaliações pendentes:</span>
                                    <span class="font-medium text-red-600 dark:text-red-400">
                                        {{ $activeEmployees->whereNull('last_evaluation_date')->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Department Performance Chart --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Distribuição da Equipe</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Atualizado em tempo real</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Status Distribution --}}
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Por Status</h4>
                            <div class="space-y-2">
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600 dark:text-gray-400">Ativos</span>
                                        <span
                                            class="text-gray-900 dark:text-gray-100">{{ $activeEmployees->count() }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full"
                                            style="width: {{ $departmentEmployees->count() > 0 ? ($activeEmployees->count() / $departmentEmployees->count()) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600 dark:text-gray-400">Férias</span>
                                        <span
                                            class="text-gray-900 dark:text-gray-100">{{ $onVacation->count() }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-blue-500 h-2 rounded-full"
                                            style="width: {{ $departmentEmployees->count() > 0 ? ($onVacation->count() / $departmentEmployees->count()) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600 dark:text-gray-400">Afastados</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ $onLeave->count() }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-red-500 h-2 rounded-full"
                                            style="width: {{ $departmentEmployees->count() > 0 ? ($onLeave->count() / $departmentEmployees->count()) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Education Level Distribution --}}
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Por Escolaridade</h4>
                            @php
                                $educationLevels = [
                                    'bachelor' => 'Superior',
                                    'postgraduate' => 'Pós-Graduação',
                                    'master' => 'Mestrado',
                                    'doctorate' => 'Doutorado',
                                    'technical' => 'Técnico',
                                    'high_school' => 'Ensino Médio',
                                ];
                            @endphp
                            <div class="space-y-2">
                                @foreach ($educationLevels as $key => $label)
                                    @php
                                        $count = $activeEmployees->where('education_level', $key)->count();
                                        $percentage =
                                            $activeEmployees->count() > 0
                                                ? ($count / $activeEmployees->count()) * 100
                                                : 0;
                                    @endphp
                                    @if ($count > 0)
                                        <div>
                                            <div class="flex justify-between text-sm mb-1">
                                                <span
                                                    class="text-gray-600 dark:text-gray-400">{{ $label }}</span>
                                                <span
                                                    class="text-gray-900 dark:text-gray-100">{{ $count }}</span>
                                            </div>
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                <div class="bg-purple-500 h-2 rounded-full"
                                                    style="width: {{ $percentage }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
