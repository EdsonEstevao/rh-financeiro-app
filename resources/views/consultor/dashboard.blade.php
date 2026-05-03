{{-- resources/views/consultor/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard do Consultor') }}
            </h2>
            <div class="flex items-center space-x-3">
                <span
                    class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                    Consultor
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ auth()->user()->name }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Welcome Card --}}
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 overflow-hidden shadow-sm rounded-lg mb-8">
                <div class="p-6 sm:p-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-white">
                                Bem-vindo, {{ auth()->user()->name }}!
                            </h3>
                            <p class="mt-2 text-indigo-100">
                                Área do Consultor • {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
                            </p>
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ações Rápidas</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <a href="{{ route('consultor.clients') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700 group">
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 group-hover:scale-110 transition">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <span class="mt-2 text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Meus
                            Clientes</span>
                    </a>

                    <a href="{{ route('financeiro.boletos.index') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700 group">
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900 group-hover:scale-110 transition">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span
                            class="mt-2 text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Boletos</span>
                    </a>

                    <a href="{{ route('financeiro.credit-cards.index') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700 group">
                        <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900 group-hover:scale-110 transition">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <span
                            class="mt-2 text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Cartões</span>
                    </a>

                    <a href="{{ route('rh.employees.index') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700 group">
                        <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900 group-hover:scale-110 transition">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span
                            class="mt-2 text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Funcionários</span>
                    </a>

                    <a href="{{ route('financeiro.reports.index') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700 group">
                        <div class="p-3 rounded-full bg-pink-100 dark:bg-pink-900 group-hover:scale-110 transition">
                            <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span
                            class="mt-2 text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Relatórios</span>
                    </a>

                    <a href="{{ route('profile.edit') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700 group">
                        <div class="p-3 rounded-full bg-gray-100 dark:bg-gray-900 group-hover:scale-110 transition">
                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <span class="mt-2 text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Meu
                            Perfil</span>
                    </a>
                </div>
            </div>

            {{-- Overview Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                @php
                    // Dados de exemplo - substituir por queries reais
                    $totalClients = \App\Models\User::role('funcionario')->count();
                    $activeClients = \App\Models\User::role('funcionario')->where('is_active', true)->count();

                    $totalBoletos = \App\Models\Boleto::count();
                    $pendingBoletos = \App\Models\Boleto::where('status', 'pending')->count();
                    $paidThisMonth = \App\Models\Boleto::where('status', 'paid')
                        ->whereMonth('paid_at', now()->month)
                        ->sum('amount');

                    $creditCardVolume = \App\Models\CreditCardTransaction::where('status', 'approved')
                        ->whereMonth('created_at', now()->month)
                        ->sum('amount');

                    $newClientsThisMonth = \App\Models\User::role('funcionario')
                        ->whereMonth('created_at', now()->month)
                        ->count();
                @endphp

                {{-- Total Clients --}}
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
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total de Clientes</h3>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $totalClients }}</p>
                                    <p class="ml-2 text-sm text-green-600 dark:text-green-400">
                                        +{{ $newClientsThisMonth }} este mês</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>Ativos: {{ $activeClients }}</span>
                                <span>{{ $totalClients > 0 ? round(($activeClients / $totalClients) * 100) : 0 }}%
                                    ativos</span>
                            </div>
                            <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full"
                                    style="width: {{ $totalClients > 0 ? ($activeClients / $totalClients) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Boletos Pendentes --}}
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg transform hover:scale-105 transition duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Boletos Pendentes</h3>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $pendingBoletos }}</p>
                                    <p class="ml-2 text-sm text-gray-500 dark:text-gray-400">de {{ $totalBoletos }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>Pagos este mês</span>
                                <span class="font-semibold text-green-600 dark:text-green-400">R$
                                    {{ number_format($paidThisMonth, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Credit Card Volume --}}
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg transform hover:scale-105 transition duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-full bg-green-100 dark:bg-green-900">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Volume Cartão (Mês)
                                </h3>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">R$
                                        {{ number_format($creditCardVolume, 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Transações aprovadas em {{ now()->translatedFormat('F/Y') }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Commission Card --}}
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg transform hover:scale-105 transition duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Comissão Estimada</h3>
                                @php
                                    $commissionRate = 0.05; // 5% de comissão
                                    $estimatedCommission = ($paidThisMonth + $creditCardVolume) * $commissionRate;
                                @endphp
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">R$
                                        {{ number_format($estimatedCommission, 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Base: 5% sobre R$ {{ number_format($paidThisMonth + $creditCardVolume, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Clients & Boletos --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

                {{-- Recent Clients --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Clientes Recentes</h3>
                            <a href="{{ route('consultor.clients') }}"
                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                Ver todos →
                            </a>
                        </div>

                        @php
                            $recentClients = \App\Models\User::role('funcionario')
                                ->with('employee.department')
                                ->latest()
                                ->take(5)
                                ->get();
                        @endphp

                        @if ($recentClients->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Cliente</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                CPF</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Departamento</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Status</th>
                                            <th
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($recentClients as $client)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-8 w-8">
                                                            <div
                                                                class="h-8 w-8 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold text-xs">
                                                                {{ strtoupper(substr($client->name, 0, 2)) }}
                                                            </div>
                                                        </div>
                                                        <div class="ml-3">
                                                            <div
                                                                class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                                {{ $client->name }}</div>
                                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                                {{ $client->email }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                                    {{ $client->cpf ?? '---' }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $client->employee?->department?->name ?? '---' }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span
                                                        class="px-2 py-1 text-xs font-semibold rounded-full
                                                        {{ $client->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                        {{ $client->is_active ? 'Ativo' : 'Inativo' }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                                    <a href="{{ route('consultor.clients.show', $client) }}"
                                                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm">
                                                        Detalhes →
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
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nenhum cliente encontrado</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Upcoming Due Boletos --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Boletos a Vencer</h3>

                        @php
                            $upcomingBoletos = \App\Models\Boleto::with('user')
                                ->where('status', 'pending')
                                ->where('due_date', '>=', now())
                                ->where('due_date', '<=', now()->addDays(15))
                                ->orderBy('due_date')
                                ->take(5)
                                ->get();
                        @endphp

                        @if ($upcomingBoletos->count() > 0)
                            <div class="space-y-3">
                                @foreach ($upcomingBoletos as $boleto)
                                    <div
                                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                {{ $boleto->user->name ?? $boleto->payer_name }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Vence: {{ $boleto->due_date->format('d/m/Y') }}
                                                <span class="text-indigo-600 dark:text-indigo-400 font-medium">
                                                    ({{ now()->diffInDays($boleto->due_date) }} dias)
                                                </span>
                                            </p>
                                        </div>
                                        <div class="ml-3 text-right">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                R$ {{ number_format($boleto->amount, 2, ',', '.') }}
                                            </p>
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
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nenhum boleto próximo</p>
                            </div>
                        @endif

                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('financeiro.boletos.index') }}"
                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                Ver todos os boletos →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Monthly Performance --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Performance Mensal</h3>
                        <span
                            class="text-xs text-gray-500 dark:text-gray-400">{{ now()->translatedFormat('F/Y') }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Boletos Performance --}}
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Boletos</h4>
                            <div class="space-y-2">
                                @php
                                    $boletoStats = [
                                        'Emitidos' => \App\Models\Boleto::whereMonth(
                                            'created_at',
                                            now()->month,
                                        )->count(),
                                        'Pagos' => \App\Models\Boleto::where('status', 'paid')
                                            ->whereMonth('paid_at', now()->month)
                                            ->count(),
                                        'Pendentes' => \App\Models\Boleto::where('status', 'pending')
                                            ->whereMonth('created_at', now()->month)
                                            ->count(),
                                        'Vencidos' => \App\Models\Boleto::where('status', 'overdue')
                                            ->whereMonth('due_date', now()->month)
                                            ->count(),
                                    ];
                                @endphp
                                @foreach ($boletoStats as $label => $value)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">{{ $label }}</span>
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Credit Card Performance --}}
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Cartão de Crédito
                            </h4>
                            <div class="space-y-2">
                                @php
                                    $cardStats = [
                                        'Transações' => \App\Models\CreditCardTransaction::whereMonth(
                                            'created_at',
                                            now()->month,
                                        )->count(),
                                        'Aprovadas' => \App\Models\CreditCardTransaction::where('status', 'approved')
                                            ->whereMonth('created_at', now()->month)
                                            ->count(),
                                        'Volume Total' =>
                                            'R$ ' .
                                            number_format(
                                                \App\Models\CreditCardTransaction::whereMonth(
                                                    'created_at',
                                                    now()->month,
                                                )->sum('amount'),
                                                2,
                                                ',',
                                                '.',
                                            ),
                                        'Ticket Médio' =>
                                            'R$ ' .
                                            number_format(
                                                \App\Models\CreditCardTransaction::whereMonth(
                                                    'created_at',
                                                    now()->month,
                                                )->avg('amount') ?? 0,
                                                2,
                                                ',',
                                                '.',
                                            ),
                                    ];
                                @endphp
                                @foreach ($cardStats as $label => $value)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">{{ $label }}</span>
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Client Performance --}}
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Clientes</h4>
                            <div class="space-y-2">
                                @php
                                    $clientStats = [
                                        'Novos este mês' => \App\Models\User::role('funcionario')
                                            ->whereMonth('created_at', now()->month)
                                            ->count(),
                                        'Total Ativos' => \App\Models\User::role('funcionario')
                                            ->where('is_active', true)
                                            ->count(),
                                        'Com Boletos' => \App\Models\User::role('funcionario')
                                            ->whereHas('boletos', function ($q) {
                                                $q->whereMonth('created_at', now()->month);
                                            })
                                            ->count(),
                                        'Inadimplentes' => \App\Models\User::role('funcionario')
                                            ->whereHas('boletos', function ($q) {
                                                $q->where('status', 'overdue')->orWhere(function ($sq) {
                                                    $sq->where('status', 'pending')->where('due_date', '<', now());
                                                });
                                            })
                                            ->count(),
                                    ];
                                @endphp
                                @foreach ($clientStats as $label => $value)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">{{ $label }}</span>
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
