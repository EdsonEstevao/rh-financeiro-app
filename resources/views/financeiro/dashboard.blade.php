{{-- resources/views/financeiro/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard Financeiro') }}
            </h2>
            <div class="flex items-center space-x-3">
                <span
                    class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    Financeiro
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ now()->translatedFormat('l, d/m/Y') }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Quick Actions --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ações Rápidas</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <a href="{{ route('financeiro.boletos.create') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mb-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Novo Boleto</span>
                    </a>
                    <a href="{{ route('financeiro.boletos.index') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400 mb-2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Boletos</span>
                    </a>
                    <a href="{{ route('financeiro.credit-cards.index') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400 mb-2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Cartões</span>
                    </a>
                    <a href="{{ route('financeiro.reports.index') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400 mb-2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Relatórios</span>
                    </a>
                    <a href="{{ route('financeiro.reports.cash-flow') }}"
                        class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition border border-gray-200 dark:border-gray-700">
                        <svg class="w-8 h-8 text-pink-600 dark:text-pink-400 mb-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Fluxo Caixa</span>
                    </a>
                </div>
            </div>

            {{-- Overview Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                @php
                    $today = now()->today();
                    $monthStart = now()->startOfMonth();

                    $todayBoletos = \App\Models\Boleto::whereDate('created_at', $today);
                    $monthBoletos = \App\Models\Boleto::whereDate('created_at', '>=', $monthStart);
                    $pendingBoletos = \App\Models\Boleto::where('status', 'pending');
                    $overdueBoletos = \App\Models\Boleto::where('status', 'overdue')->orWhere(
                        fn($q) => $q->where('status', 'pending')->where('due_date', '<', now()),
                    );
                    $paidMonth = \App\Models\Boleto::where('status', 'paid')->whereDate('paid_at', '>=', $monthStart);
                    $cardMonth = \App\Models\CreditCardTransaction::whereDate('created_at', '>=', $monthStart);
                    $cardApproved = (clone $cardMonth)->where('status', 'approved');
                @endphp

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 transform hover:scale-105 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Receita do Mês</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                R$
                                {{ number_format($paidMonth->sum('amount') + $cardApproved->sum('net_amount'), 2, ',', '.') }}
                            </p>
                        </div>
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">{{ $paidMonth->count() }} boletos pagos</p>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 transform hover:scale-105 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Pendentes</p>
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                R$ {{ number_format($pendingBoletos->sum('amount'), 2, ',', '.') }}
                            </p>
                        </div>
                        <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">{{ $pendingBoletos->count() }} boletos pendentes</p>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 transform hover:scale-105 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Vencidos</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                                R$ {{ number_format($overdueBoletos->sum('amount'), 2, ',', '.') }}
                            </p>
                        </div>
                        <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">{{ $overdueBoletos->count() }} boletos vencidos</p>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 transform hover:scale-105 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Transações Cartão</p>
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                {{ $cardMonth->count() }}
                            </p>
                        </div>
                        <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">R$
                        {{ number_format($cardMonth->sum('amount'), 2, ',', '.') }}</p>
                </div>
            </div>

            {{-- Recent Boletos & Transactions --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                {{-- Recent Boletos --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Boletos Recentes</h3>
                            <a href="{{ route('financeiro.boletos.index') }}"
                                class="text-sm text-blue-600 hover:underline">Ver todos →</a>
                        </div>
                        <div class="space-y-3">
                            @forelse(\App\Models\Boleto::with('user')->latest()->take(5)->get() as $boleto)
                                <div
                                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                            {{ $boleto->user->name ?? $boleto->payer_name }}
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $boleto->boleto_number }} • Venc:
                                            {{ $boleto->due_date->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="ml-3 text-right">
                                        <p class="text-sm font-semibold">R$
                                            {{ number_format($boleto->amount, 2, ',', '.') }}</p>
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full {{ $boleto->status === 'paid' ? 'bg-green-100 text-green-800' : ($boleto->isOverdue() ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ $boleto->status === 'paid' ? 'Pago' : ($boleto->isOverdue() ? 'Vencido' : 'Pendente') }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-gray-500 py-4">Nenhum boleto encontrado</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Recent Transactions --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Transações Recentes</h3>
                            <a href="{{ route('financeiro.credit-cards.index') }}"
                                class="text-sm text-blue-600 hover:underline">Ver todas →</a>
                        </div>
                        <div class="space-y-3">
                            @forelse(\App\Models\CreditCardTransaction::with('user')->latest()->take(5)->get() as $t)
                                <div
                                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $t->customer_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $t->masked_card_number }} •
                                            {{ strtoupper($t->card_brand) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold">R$
                                            {{ number_format($t->amount, 2, ',', '.') }}</p>
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full {{ $t->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($t->status) }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-gray-500 py-4">Nenhuma transação</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Receitas dos Últimos 7 Dias
                    </h3>
                    <div class="h-64" x-data="revenueChart()" x-init="initChart()">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            function revenueChart() {
                return {
                    initChart() {
                        const ctx = document.getElementById('revenueChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: @json($labels ?? []),
                                datasets: [{
                                    label: 'Boletos',
                                    data: @json($boletoData ?? []),
                                    backgroundColor: '#3B82F6'
                                }, {
                                    label: 'Cartões',
                                    data: @json($cardData ?? []),
                                    backgroundColor: '#8B5CF6'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        });
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
