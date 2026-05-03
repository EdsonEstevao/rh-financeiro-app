{{-- resources/views/admin/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard Administrativo') }}
            </h2>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span>Último acesso:
                    {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('d/m/Y H:i') : 'Primeiro acesso' }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Quick Actions --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ações Rápidas</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <a href="{{ route('admin.users.index') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mb-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Usuários</span>
                    </a>

                    <a href="{{ route('rh.employees.index') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400 mb-2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span
                            class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Funcionários</span>
                    </a>

                    <a href="{{ route('financeiro.boletos.index') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400 mb-2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Boletos</span>
                    </a>

                    <a href="{{ route('admin.audit.index') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400 mb-2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Auditoria</span>
                    </a>

                    <a href="{{ route('admin.settings') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-gray-600 dark:text-gray-400 mb-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span
                            class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Configurações</span>
                    </a>

                    <a href="{{ route('rh.reports.index') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-red-600 dark:text-red-400 mb-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Relatórios</span>
                    </a>
                </div>
            </div>

            {{-- System Overview Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {{-- Total Users Card --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total de Usuários</h3>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $stats['total_users'] ?? 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>Ativos: {{ \App\Models\User::where('is_active', true)->count() }}</span>
                                <span>Inativos: {{ \App\Models\User::where('is_active', false)->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Employees Card --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-full bg-green-100 dark:bg-green-900">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Funcionários Ativos
                                </h3>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $stats['active_employees'] ?? 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>Total: {{ $stats['total_employees'] ?? 0 }}</span>
                                <span>Desligados:
                                    {{ ($stats['total_employees'] ?? 0) - ($stats['active_employees'] ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Financial Overview Card --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Receita do Mês</h3>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        R$ {{ number_format($stats['total_revenue'] ?? 0, 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>Boletos Pendentes: {{ $stats['pending_boletos'] ?? 0 }}</span>
                                <span>Cartões Hoje:
                                    {{ \App\Models\CreditCardTransaction::whereDate('created_at', today())->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- System Health Card --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status do Sistema</h3>
                                <div class="flex items-baseline">
                                    <p class="text-lg font-semibold text-green-600 dark:text-green-400">Online</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>Uptime: 99.9%</span>
                                <span>v{{ config('app.version', '1.0.0') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts and Recent Activity --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

                {{-- User Distribution by Role --}}
                <div class="lg:col-span-1 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Usuários por Perfil
                        </h3>
                        <div class="space-y-3">
                            @php
                                $roles = \Spatie\Permission\Models\Role::withCount('users')->get();
                            @endphp
                            @foreach ($roles as $role)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span
                                            class="text-gray-700 dark:text-gray-300">{{ ucfirst($role->name) }}</span>
                                        <span class="text-gray-500 dark:text-gray-400">{{ $role->users_count }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full"
                                            style="width: {{ $stats['total_users'] > 0 ? ($role->users_count / $stats['total_users']) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('admin.users.index') }}"
                                class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                Gerenciar usuários →
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Recent Activities --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Atividades Recentes
                        </h3>

                        @if (isset($stats['recent_transactions']) && $stats['recent_transactions']->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Usuário</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Ação</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Valor</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Data</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($stats['recent_transactions'] as $transaction)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-8 w-8">
                                                            <div
                                                                class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-xs">
                                                                {{ strtoupper(substr($transaction->user->name ?? 'U', 0, 2)) }}
                                                            </div>
                                                        </div>
                                                        <div class="ml-3">
                                                            <div
                                                                class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                                {{ $transaction->user->name ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                                    {{ $transaction->description ?? 'Transação' }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                    <span
                                                        class="px-2 py-1 text-xs font-semibold rounded-full
                                                        {{ ($transaction->status ?? '') === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                        R$ {{ number_format($transaction->amount ?? 0, 2, ',', '.') }}
                                                    </span>
                                                </td>
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $transaction->created_at ? $transaction->created_at->diffForHumans() : 'N/A' }}
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
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Nenhuma atividade
                                    recente</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">As atividades aparecerão aqui
                                    conforme forem realizadas.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Department Overview --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Visão Geral por Departamento
                        </h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Atualizado em {{ now()->format('d/m/Y H:i') }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @php
                            $departments = \App\Models\Department::withCount(['employees', 'activeEmployees'])->get();
                        @endphp

                        @forelse($departments as $department)
                            <div
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition duration-300">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $department->name }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $department->code }}
                                        </p>
                                    </div>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $department->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $department->is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Funcionários:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $department->active_employees_count }} /
                                            {{ $department->employees_count }}
                                        </span>
                                    </div>

                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full"
                                            style="width: {{ ($stats['active_employees'] ?? 0) > 0 ? ($department->active_employees_count / ($stats['active_employees'] ?? 1)) * 100 : 0 }}%">
                                        </div>
                                    </div>

                                    @if ($department->budget)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">Orçamento:</span>
                                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                                R$ {{ number_format($department->budget, 2, ',', '.') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 text-center py-8">
                                <p class="text-gray-500 dark:text-gray-400">Nenhum departamento cadastrado.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- System Info Footer --}}
            <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Informações do Sistema</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Versão do Laravel:</span>
                            <span
                                class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ app()->version() }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Ambiente:</span>
                            <span
                                class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ app()->environment() }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">PHP:</span>
                            <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ PHP_VERSION }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Banco de Dados:</span>
                            <span
                                class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ config('database.default') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Servidor:</span>
                            <span
                                class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Timezone:</span>
                            <span
                                class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ config('app.timezone') }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
