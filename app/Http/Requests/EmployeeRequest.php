<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Models\User;

class EmployeeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function authorize(): bool
    {
        return true; // coloque policy/gate depois se quiser
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'supervisor_id' => ['nullable', 'integer', 'exists:employees,id'],

            'position' => ['required', 'string', 'max:100'],

            'employment_type' => ['nullable', Rule::in(['clt','pj','intern','temporary','contractor'])],
            'work_schedule' => ['nullable', Rule::in(['full_time','part_time','flexible','remote'])],
            'workload_hours' => ['nullable', 'integer', 'min:1', 'max:168'],

            'salary' => ['required', 'numeric', 'min:0'],
            'salary_type' => ['nullable', Rule::in(['monthly','hourly','daily'])],

            'hire_date' => ['required', 'date'],
            'status' => ['nullable', Rule::in(['active','inactive','vacation','leave','terminated','suspended'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        // aplica defaults iguais aos do DB quando vier vazio (boa prática p/ consistência)
        $this->merge([
            'employment_type' => $this->input('employment_type', 'clt'),
            'work_schedule' => $this->input('work_schedule', 'full_time'),
            'workload_hours' => $this->input('workload_hours', 40),
            'salary_type' => $this->input('salary_type', 'monthly'),
            'status' => $this->input('status', 'active'),
        ]);
    }
}
