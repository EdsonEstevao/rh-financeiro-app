{{-- resources/views/admin/audit/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Auditoria do Sistema') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.audit.export.pdf') }}"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium">
                    Exportar PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total de Registros</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $audits->total() ?? 0 }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ações Hoje</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $todayCount ?? 0 }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Usuários Únicos</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $uniqueUsers ?? 0 }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Última Ação</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-2">
                        {{ $lastActivity ? $lastActivity->diffForHumans() : '---' }}</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Usuário</label>
                            <select name="user_id"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                @foreach (\App\Models\User::orderBy('name')->get() as $u)
                                    <option value="{{ $u->id }}"
                                        {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ação</label>
                            <select name="action"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">Todas</option>
                                <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Criado
                                </option>
                                <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>
                                    Atualizado</option>
                                <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>
                                    Deletado</option>
                                <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Login
                                </option>
                                <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>Logout
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data
                                Início</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data
                                Fim</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                Filtrar
                            </button>
                            <a href="{{ route('admin.audit.index') }}"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium">
                                Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Audit Table --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Data/Hora</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Usuário</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Ação</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Modelo/Tabela</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Descrição</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    IP</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($audits ?? [] as $audit)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                        {{ $audit->created_at->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs font-semibold">
                                                {{ strtoupper(substr($audit->user->name ?? 'S', 0, 2)) }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                    {{ $audit->user->name ?? 'Sistema' }}</div>
                                                <div class="text-xs text-gray-500">{{ $audit->user->email ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full
                                            @if (stripos($audit->event ?? '', 'creat') !== false) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif(stripos($audit->event ?? '', 'updat') !== false) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif(stripos($audit->event ?? '', 'delet') !== false) bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @elseif(stripos($audit->event ?? '', 'login') !== false) bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                            {{ ucfirst($audit->event ?? ($audit->action ?? '')) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                        {{ class_basename($audit->auditable_type ?? ($audit->model_type ?? '')) }}
                                        @if ($audit->auditable_id ?? ($audit->model_id ?? false))
                                            <span
                                                class="text-xs text-gray-500">#{{ $audit->auditable_id ?? $audit->model_id }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200 max-w-xs truncate">
                                        {{ $audit->description ?? Str::limit(json_encode($audit->new_values ?? ($audit->changes ?? '')), 80) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $audit->ip_address ?? '---' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Nenhum
                                            registro de auditoria</h3>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if (isset($audits) && method_exists($audits, 'links'))
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $audits->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
