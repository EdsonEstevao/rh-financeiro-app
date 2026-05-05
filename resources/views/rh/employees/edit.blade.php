<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Editar Funcionário #{{ $employee->id }}
            </h2>

            <div class="flex items-center gap-2">
                <a href="{{ route('rh.employees.advanced.edit', $employee) }}"
                    class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 text-sm font-medium">
                    Detalhes avançados
                </a>

                <a href="{{ route('rh.employees.index') }}"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium">
                    ← Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ showDeleteModal: false }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <form action="{{ route('rh.employees.update', $employee) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Obrigatórios</h3>

                    {{-- User readonly --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Usuário vinculado
                        </label>
                        <input type="text" readonly
                            value="{{ $employee->user->name ?? '—' }} — {{ $employee->user->email ?? '' }}"
                            class="w-full rounded-md border-gray-300 bg-gray-100 text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 shadow-sm">
                    </div>
                    <input type="hidden" name="user_id" value="{{ $employee->user_id }}">
                    <input type="hidden" name="email" value="{{ $employee->user->email ?? '' }}">


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cargo
                                *</label>
                            <input type="text" name="position" maxlength="100" required
                                value="{{ old('position', $employee->position) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                            @error('position')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Admissão
                                *</label>
                            <input type="date" name="hire_date" required
                                value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                            @error('hire_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Salário
                                *</label>
                            <input type="number" name="salary" step="0.01" min="0" required
                                value="{{ old('salary', $employee->salary) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                            @error('salary')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status
                                *</label>
                            <select name="status" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                @foreach (['active' => 'Ativo', 'inactive' => 'Inativo', 'vacation' => 'Férias', 'leave' => 'Afastado', 'terminated' => 'Desligado', 'suspended' => 'Suspenso'] as $k => $label)
                                    <option value="{{ $k }}" @selected(old('status', $employee->status) === $k)>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Configurações do vínculo
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de
                                contrato</label>
                            <select name="employment_type"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                @foreach (['clt' => 'CLT', 'pj' => 'PJ', 'intern' => 'Estágio', 'temporary' => 'Temporário', 'contractor' => 'Contratado'] as $k => $label)
                                    <option value="{{ $k }}" @selected(old('employment_type', $employee->employment_type) === $k)>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jornada</label>
                            <select name="work_schedule"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                @foreach (['full_time' => 'Integral', 'part_time' => 'Parcial', 'flexible' => 'Flexível', 'remote' => 'Remoto'] as $k => $label)
                                    <option value="{{ $k }}" @selected(old('work_schedule', $employee->work_schedule) === $k)>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Carga
                                (h/semana)</label>
                            <input type="number" name="workload_hours" min="1" max="168"
                                value="{{ old('workload_hours', $employee->workload_hours) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de
                                salário</label>
                            <select name="salary_type"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                @foreach (['monthly' => 'Mensal', 'hourly' => 'Hora', 'daily' => 'Diária'] as $k => $label)
                                    <option value="{{ $k }}" @selected(old('salary_type', $employee->salary_type) === $k)>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Departamento</label>
                            <select name="department_id"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">—</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected((string) old('department_id', $employee->department_id) === (string) $dept->id)>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Supervisor</label>
                            <select name="supervisor_id"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">—</option>
                                @foreach ($supervisors as $sup)
                                    <option value="{{ $sup->id }}" @selected((string) old('supervisor_id', $employee->supervisor_id) === (string) $sup->id)>
                                        #{{ $sup->id }} — {{ data_get($sup, 'user.name', 'Sem nome') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button"
                        class="px-6 py-3 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium"
                        @click="showDeleteModal = true">
                        Excluir
                    </button>

                    <a href="{{ route('rh.employees.index') }}"
                        class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 font-medium">
                        Cancelar
                    </a>

                    <button type="submit"
                        class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                        Salvar
                    </button>
                </div>
            </form>

            {{-- Delete fora do form --}}
            <form id="delete-employee-form" action="{{ route('rh.employees.destroy', $employee) }}" method="POST"
                class="hidden">
                @csrf
                @method('DELETE')
            </form>

            <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50" style="display:none"
                @keydown.escape.window="showDeleteModal = false">
                <div class="absolute inset-0 bg-black/50" @click="showDeleteModal = false"></div>
                <div class="relative min-h-full flex items-center justify-center p-4">
                    <div class="w-full max-w-md rounded-lg bg-white dark:bg-gray-800 shadow-xl p-6"
                        @click.outside="showDeleteModal = false">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Confirmar exclusão</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                            Excluir o funcionário #{{ $employee->id }}?
                        </p>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
                                @click="showDeleteModal = false">
                                Cancelar
                            </button>
                            <button type="button" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                                @click="document.getElementById('delete-employee-form').submit()">
                                Sim, excluir
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
