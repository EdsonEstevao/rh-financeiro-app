{{-- resources/views/rh/documents/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Documentos dos Funcionários') }}
            </h2>
            <button onclick="document.getElementById('upload-modal').classList.remove('hidden')"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm font-medium flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Upload Documento
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                @php
                    $totalDocs = \App\Models\EmployeeDocument::count();
                    $pendingDocs = \App\Models\EmployeeDocument::where('status', 'pending')->count();
                    $approvedDocs = \App\Models\EmployeeDocument::where('status', 'approved')->count();
                    $expiringDocs = \App\Models\EmployeeDocument::where('expiration_date', '<=', now()->addDays(30))
                        ->where('expiration_date', '>=', now())
                        ->where('status', '!=', 'expired')
                        ->count();
                @endphp

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Documentos</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalDocs }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pendentes Aprovação</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $pendingDocs }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Aprovados</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $approvedDocs }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Vencendo em 30 dias</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $expiringDocs }}</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Funcionário</label>
                            <select name="employee_id"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                @foreach (\App\Models\Employee::with('user')->get() as $emp)
                                    <option value="{{ $emp->id }}"
                                        {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                            <select name="type"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                <option value="rg" {{ request('type') === 'rg' ? 'selected' : '' }}>RG</option>
                                <option value="cpf" {{ request('type') === 'cpf' ? 'selected' : '' }}>CPF</option>
                                <option value="ctps" {{ request('type') === 'ctps' ? 'selected' : '' }}>CTPS</option>
                                <option value="diploma" {{ request('type') === 'diploma' ? 'selected' : '' }}>Diploma
                                </option>
                                <option value="medical" {{ request('type') === 'medical' ? 'selected' : '' }}>Atestado
                                    Médico</option>
                                <option value="contract" {{ request('type') === 'contract' ? 'selected' : '' }}>
                                    Contrato</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select name="status"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                                    Pendente</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>
                                    Aprovado</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>
                                    Rejeitado</option>
                                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Vencido
                                </option>
                            </select>
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">Filtrar</button>
                            <a href="{{ route('rh.documents.index') }}"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium">Limpar</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Documents Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Documento</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Funcionário</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Tipo</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Validade</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($documents ?? [] as $doc)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <span
                                                class="text-sm font-medium text-gray-900 dark:text-gray-200">{{ $doc->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                        {{ $doc->employee->user->name ?? '---' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ ucfirst($doc->type) }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm {{ $doc->isExpired() ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-500' }}">
                                        {{ $doc->expiration_date?->format('d/m/Y') ?? '---' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full
                                            @if ($doc->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($doc->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif($doc->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                            {{ ucfirst($doc->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex justify-end space-x-1">
                                            <a href="{{ route('rh.documents.download', $doc) }}"
                                                class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg"
                                                title="Download">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </a>
                                            @if ($doc->status === 'pending')
                                                <form action="{{ route('rh.documents.approve', $doc) }}" method="POST"
                                                    class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="p-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg"
                                                        title="Aprovar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </button>
                                                </form>
                                                <button onclick="rejectDoc({{ $doc->id }})"
                                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg"
                                                    title="Rejeitar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">Nenhum documento
                                        encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if (isset($documents) && method_exists($documents, 'links'))
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">{{ $documents->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
