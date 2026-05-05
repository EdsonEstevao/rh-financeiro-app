{{-- resources/views/rh/employees/_form.blade.php --}}
@php
    /** @var \App\Models\Employee|null $employee */

    $isEdit = ($mode ?? 'create') === 'edit';

    // Helper para obter valor com old() + fallback no employee
    $val = function (string $key, $default = null) use ($employee) {
        $employeeValue = data_get($employee, $key, $default);
        return old($key, $employeeValue);
    };

    // Para JSONs (skills, languages, certifications): garantir array
    $arr = function (string $key) use ($val) {
        $v = $val($key);
        if (is_string($v)) {
            return array_values(array_filter(array_map('trim', explode(',', $v))));
        }
        if (is_array($v)) {
            return $v;
        }
        return $v ? (array) $v : [];
    };
@endphp

<div class="space-y-6">

    {{-- Identificação / Relacionamentos --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Vínculos</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Usuário (user_id)
                    *</label>
                <input type="number" name="user_id" min="1" required value="{{ $val('user_id') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                @error('user_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Boa prática: substituir por um select/autocomplete de usuários.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Departamento</label>
                <select name="department_id"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                    <option value="">—</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" @selected((string) $val('department_id') === (string) $dept->id)>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Supervisor</label>
                <select name="supervisor_id"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                    <option value="">—</option>
                    @foreach ($supervisors as $sup)
                        <option value="{{ $sup->id }}" @selected((string) $val('supervisor_id') === (string) $sup->id)>
                            #{{ $sup->id }} — {{ data_get($sup, 'user.name', 'Sem nome') }} ({{ $sup->position }})
                        </option>
                    @endforeach
                </select>
                @error('supervisor_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Informações Profissionais --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Informações Profissionais</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Matrícula</label>
                <input type="text" name="registration_number" maxlength="50"
                    value="{{ $val('registration_number') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                @error('registration_number')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cargo *</label>
                <input type="text" name="position" maxlength="100" required value="{{ $val('position') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                @error('position')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Função</label>
                <input type="text" name="role" maxlength="100" value="{{ $val('role') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                @error('role')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de contrato
                    *</label>
                <select name="employment_type" required
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                    @foreach (['clt' => 'CLT', 'pj' => 'PJ', 'intern' => 'Estágio', 'temporary' => 'Temporário', 'contractor' => 'Contratado'] as $k => $label)
                        <option value="{{ $k }}" @selected($val('employment_type', 'clt') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('employment_type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jornada *</label>
                <select name="work_schedule" required
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                    @foreach (['full_time' => 'Integral', 'part_time' => 'Parcial', 'flexible' => 'Flexível', 'remote' => 'Remoto'] as $k => $label)
                        <option value="{{ $k }}" @selected($val('work_schedule', 'full_time') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('work_schedule')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Carga (h/semana)
                    *</label>
                <input type="number" name="workload_hours" min="1" max="168" required
                    value="{{ $val('workload_hours', 40) }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                @error('workload_hours')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Salário --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Salário e Benefícios</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Salário *</label>
                <input type="number" name="salary" step="0.01" min="0" required
                    value="{{ $val('salary') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                @error('salary')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                <select name="salary_type"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                    @foreach (['monthly' => 'Mensal', 'hourly' => 'Hora', 'daily' => 'Diária'] as $k => $label)
                        <option value="{{ $k }}" @selected($val('salary_type', 'monthly') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('salary_type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor benefícios</label>
                <input type="number" name="benefits_value" step="0.01" min="0"
                    value="{{ $val('benefits_value', 0) }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                @error('benefits_value')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            @php
                $checks = [
                    'has_health_plan' => 'Plano de saúde',
                    'has_dental_plan' => 'Plano odontológico',
                    'has_life_insurance' => 'Seguro de vida',
                    'has_meal_voucher' => 'Vale refeição',
                    'has_food_voucher' => 'Vale alimentação',
                    'has_transportation_voucher' => 'Vale transporte',
                    'has_gym_pass' => 'Gympass',
                ];
            @endphp

            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach ($checks as $name => $label)
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="{{ $name }}" value="1" @checked((bool) $val($name, false))
                            class="rounded border-gray-300 dark:border-gray-600">
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor VR</label>
                <input type="number" name="meal_voucher_value" step="0.01" min="0"
                    value="{{ $val('meal_voucher_value') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                @error('meal_voucher_value')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor VA</label>
                <input type="number" name="food_voucher_value" step="0.01" min="0"
                    value="{{ $val('food_voucher_value') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                @error('food_voucher_value')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor VT</label>
                <input type="number" name="transportation_voucher_value" step="0.01" min="0"
                    value="{{ $val('transportation_voucher_value') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                @error('transportation_voucher_value')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Datas / Status --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Status e Datas</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Admissão *</label>
                <input type="date" name="hire_date" required value="{{ $val('hire_date') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                @error('hire_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Experiência
                    (fim)</label>
                <input type="date" name="probation_end_date" value="{{ $val('probation_end_date') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                @error('probation_end_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Última promoção</label>
                <input type="date" name="last_promotion_date" value="{{ $val('last_promotion_date') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                @error('last_promotion_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                <select name="status" required
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                    @foreach (['active' => 'Ativo', 'inactive' => 'Inativo', 'vacation' => 'Férias', 'leave' => 'Afastado', 'terminated' => 'Desligado', 'suspended' => 'Suspenso'] as $k => $label)
                        <option value="{{ $k }}" @selected($val('status', 'active') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Desligamento</label>
                <input type="date" name="termination_date" value="{{ $val('termination_date') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                @error('termination_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Férias (início)</label>
                <input type="date" name="vacation_start_date" value="{{ $val('vacation_start_date') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Férias (fim)</label>
                <input type="date" name="vacation_end_date" value="{{ $val('vacation_end_date') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dias férias</label>
                <input type="number" name="vacation_days_available" min="0"
                    value="{{ $val('vacation_days_available', 30) }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dias atestado</label>
                <input type="number" name="sick_days_available" min="0"
                    value="{{ $val('sick_days_available', 15) }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
            </div>
        </div>
    </div>

    {{-- Contato / Endereço --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Contato e Endereço</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email pessoal</label>
                <input type="email" name="personal_email" maxlength="100" value="{{ $val('personal_email') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telefone</label>
                <input type="text" name="phone" maxlength="20" value="{{ $val('phone') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Celular</label>
                <input type="text" name="mobile" maxlength="20" value="{{ $val('mobile') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Endereço</label>
                <textarea name="address" rows="2"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">{{ $val('address') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CEP</label>
                <input type="text" name="zip_code" maxlength="10" value="{{ $val('zip_code') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cidade</label>
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

    {{-- Extras (JSON com Alpine: skills/certifications/languages) --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6" x-data="chipsForm({
        skills: @js($arr('skills')),
        certifications: @js($arr('certifications')),
        languages: @js($arr('languages')),
    })">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Habilidades e Idiomas</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Skills --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Habilidades</label>
                <div class="flex gap-2">
                    <input type="text" x-model="inputs.skills" @keydown.enter.prevent="add('skills')"
                        placeholder="Digite e Enter"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                    <button type="button" @click="add('skills')"
                        class="px-3 py-2 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                        +
                    </button>
                </div>
                <div class="mt-2 flex flex-wrap gap-2">
                    <template x-for="(item, idx) in lists.skills" :key="'s' + idx">
                        <span
                            class="inline-flex items-center gap-2 px-2 py-1 rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100 text-xs">
                            <span x-text="item"></span>
                            <button type="button" class="text-xs" @click="remove('skills', idx)">x</button>
                        </span>
                    </template>
                </div>
                <input type="hidden" name="skills" :value="JSON.stringify(lists.skills)">
            </div>

            {{-- Certifications --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Certificações</label>
                <div class="flex gap-2">
                    <input type="text" x-model="inputs.certifications"
                        @keydown.enter.prevent="add('certifications')" placeholder="Digite e Enter"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                    <button type="button" @click="add('certifications')"
                        class="px-3 py-2 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                        +
                    </button>
                </div>
                <div class="mt-2 flex flex-wrap gap-2">
                    <template x-for="(item, idx) in lists.certifications" :key="'c' + idx">
                        <span
                            class="inline-flex items-center gap-2 px-2 py-1 rounded bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100 text-xs">
                            <span x-text="item"></span>
                            <button type="button" class="text-xs" @click="remove('certifications', idx)">x</button>
                        </span>
                    </template>
                </div>
                <input type="hidden" name="certifications" :value="JSON.stringify(lists.certifications)">
            </div>

            {{-- Languages --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Idiomas</label>
                <div class="flex gap-2">
                    <input type="text" x-model="inputs.languages" @keydown.enter.prevent="add('languages')"
                        placeholder="Digite e Enter"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                    <button type="button" @click="add('languages')"
                        class="px-3 py-2 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                        +
                    </button>
                </div>
                <div class="mt-2 flex flex-wrap gap-2">
                    <template x-for="(item, idx) in lists.languages" :key="'l' + idx">
                        <span
                            class="inline-flex items-center gap-2 px-2 py-1 rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100 text-xs">
                            <span x-text="item"></span>
                            <button type="button" class="text-xs" @click="remove('languages', idx)">x</button>
                        </span>
                    </template>
                </div>
                <input type="hidden" name="languages" :value="JSON.stringify(lists.languages)">
            </div>
        </div>

        <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">
            Estes campos são enviados como JSON (array). Garanta que sua validação aceite JSON.
        </p>
    </div>

    {{-- Observações --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Observações</h3>
        <textarea name="observations" rows="3"
            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">{{ $val('observations') }}</textarea>
        @error('observations')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

</div>

{{-- Alpine helper --}}
@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('chipsForm', (initial) => ({
                    lists: {
                        skills: Array.isArray(initial.skills) ? initial.skills : [],
                        certifications: Array.isArray(initial.certifications) ? initial.certifications : [],
                        languages: Array.isArray(initial.languages) ? initial.languages : [],
                    },
                    inputs: {
                        skills: '',
                        certifications: '',
                        languages: ''
                    },

                    add(listName) {
                        const value = (this.inputs[listName] || '').trim();
                        if (!value) return;
                        if (this.lists[listName].includes(value)) {
                            this.inputs[listName] = '';
                            return;
                        }
                        this.lists[listName].push(value);
                        this.inputs[listName] = '';
                    },

                    remove(listName, idx) {
                        this.lists[listName].splice(idx, 1);
                    },
                }));
            });
        </script>
    @endpush
@endonce
