<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detalhes avançados — Funcionário #{{ $employee->id }}
            </h2>

            <a href="{{ route('rh.employees.edit', $employee) }}"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium">
                ← Voltar ao básico
            </a>
        </div>
    </x-slot>

    @php
        $val = fn(string $k, $d = null) => old($k, data_get($employee, $k, $d));

        // Dependentes: garantir array
        $deps = $val('dependents_info', []);
        if (is_string($deps)) {
            $decoded = json_decode($deps, true);
            $deps = is_array($decoded) ? $decoded : [];
        } elseif (!is_array($deps)) {
            $deps = [];
        }
    @endphp

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('rh.employees.advanced.update', $employee) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Pessoais --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Dados pessoais</h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">RG</label>
                            <input type="text" name="rg" maxlength="20" value="{{ $val('rg') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Emissor</label>
                            <input type="text" name="issuer" maxlength="20" value="{{ $val('issuer') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nascimento</label>
                            <input type="date" name="birth_date" value="{{ $val('birth_date') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gênero</label>
                            <select name="gender"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">—</option>
                                @foreach (['male' => 'Masculino', 'female' => 'Feminino', 'other' => 'Outro'] as $k => $label)
                                    <option value="{{ $k }}" @selected($val('gender') === $k)>{{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado
                                civil</label>
                            <select name="marital_status"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">—</option>
                                @foreach (['single' => 'Solteiro(a)', 'married' => 'Casado(a)', 'divorced' => 'Divorciado(a)', 'widowed' => 'Viúvo(a)', 'stable_union' => 'União estável'] as $k => $label)
                                    <option value="{{ $k }}" @selected($val('marital_status') === $k)>{{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nacionalidade</label>
                            <input type="text" name="nationality" maxlength="50"
                                value="{{ $val('nationality', 'Brasileiro(a)') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Naturalidade</label>
                            <input type="text" name="birth_place" maxlength="100" value="{{ $val('birth_place') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                    </div>
                </div>

                {{-- Contato / Endereço --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Contato e endereço</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email
                                pessoal</label>
                            <input type="email" name="personal_email" maxlength="100"
                                value="{{ $val('personal_email') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telefone</label>
                            <input type="text" name="phone" maxlength="20" value="{{ $val('phone') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Celular</label>
                            <input type="text" name="mobile" maxlength="20" value="{{ $val('mobile') }}"
                                x-mask="99 9 9999-9999"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Endereço</label>
                            <textarea name="address" rows="2"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">{{ $val('address') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CEP</label>
                            <input type="text" name="zip_code" maxlength="10" value="{{ $val('zip_code') }}"
                                x-mask="99.999-999"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cidade</label>
                            <input type="text" name="city" maxlength="100" value="{{ $val('city') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">UF</label>
                            <input type="text" name="state" maxlength="2" value="{{ $val('state') }}"
                                class="w-full uppercase rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                    </div>
                </div>

                {{-- Dependentes (JSON) --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6" x-data="dependentsEditor({ initial: @js($deps) })">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Dependentes</h3>

                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="has_dependents" value="1" x-model="hasDependents"
                                class="rounded border-gray-300 dark:border-gray-600">
                            <span>Possui dependentes</span>
                        </label>
                    </div>

                    <div class="mt-4 space-y-3" x-show="hasDependents" x-cloak>
                        <button type="button"
                            class="px-3 py-2 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm"
                            @click="add()">
                            + Adicionar dependente
                        </button>

                        <template x-for="(dep, idx) in dependents" :key="idx">
                            <div
                                class="grid grid-cols-1 md:grid-cols-4 gap-3 p-3 rounded border border-gray-200 dark:border-gray-700">
                                <div class="md:col-span-2">
                                    <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Nome</label>
                                    <input type="text" x-model="dep.name"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Parentesco</label>
                                    <input type="text" x-model="dep.relationship"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Nascimento</label>
                                    <input type="date" x-model="dep.birth_date"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                </div>

                                <div class="md:col-span-4 flex justify-end">
                                    <button type="button" class="text-sm text-red-600 hover:underline"
                                        @click="remove(idx)">
                                        Remover
                                    </button>
                                </div>
                            </div>
                        </template>

                        <input type="hidden" name="dependents_info" :value="JSON.stringify(dependents)">
                    </div>
                </div>

                {{-- Bancário / Fiscal / Educação / Avaliação / Metadata (exemplos diretos) --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Banco / Fiscal / Educação
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Banco</label>
                            <input type="text" name="bank_name" maxlength="50" value="{{ $val('bank_name') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Código</label>
                            <input type="text" name="bank_code" maxlength="10" value="{{ $val('bank_code') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">PIX</label>
                            <input type="text" name="pix_key" maxlength="100" value="{{ $val('pix_key') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">PIS/PASEP</label>
                            <input type="text" name="pis_pasep" maxlength="20" value="{{ $val('pis_pasep') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm"
                                x-mask="999.99999.99-9">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CTPS</label>
                            <input type="text" name="ctps" maxlength="20" value="{{ $val('ctps') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CTPS
                                Série</label>
                            <input type="text" name="ctps_serie" maxlength="20" value="{{ $val('ctps_serie') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Escolaridade</label>
                            <select name="education_level"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                <option value="">—</option>
                                @foreach ([
        'elementary' => 'Fundamental',
        'high_school' => 'Médio',
        'technical' => 'Técnico',
        'bachelor' => 'Graduação',
        'postgraduate' => 'Pós',
        'master' => 'Mestrado',
        'doctorate' => 'Doutorado',
    ] as $k => $label)
                                    <option value="{{ $k }}" @selected($val('education_level') === $k)>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instituição</label>
                            <input type="text" name="institution" maxlength="100"
                                value="{{ $val('institution') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Curso</label>
                            <input type="text" name="course" maxlength="100" value="{{ $val('course') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Skills -
                                Habilidades
                                (JSON)</label>
                            <textarea name="skills" rows="3"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">{{ is_string($val('skills')) ? $val('skills') : json_encode($val('skills'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Envie como JSON array, ex:
                                ["Excel","SQL"]</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Metadata
                                (JSON)</label>
                            <textarea name="metadata" rows="3"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">{{ is_string($val('metadata')) ? $val('metadata') : json_encode($val('metadata'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('rh.employees.edit', $employee) }}"
                        class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 font-medium">
                        Cancelar
                    </a>

                    <button type="submit"
                        class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                        Salvar avançado
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('dependentsEditor', ({
                    initial
                }) => ({
                    hasDependents: {{ $employee->has_dependents ? 'true' : 'false' }},
                    dependents: Array.isArray(initial) ? initial : [],

                    add() {
                        this.dependents.push({
                            name: '',
                            relationship: '',
                            birth_date: ''
                        });
                    },

                    remove(idx) {
                        this.dependents.splice(idx, 1);
                        if (this.dependents.length === 0) this.hasDependents = false;
                    },
                }));
            });
        </script>
    @endpush
</x-app-layout>
