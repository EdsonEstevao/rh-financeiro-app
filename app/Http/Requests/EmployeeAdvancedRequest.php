<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;



class EmployeeAdvancedRequest extends FormRequest
{
     public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Pessoais
            'rg' => ['nullable', 'string', 'max:20'],
            'issuer' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male','female','other'])],
            'marital_status' => ['nullable', Rule::in(['single','married','divorced','widowed','stable_union'])],
            'nationality' => ['nullable', 'string', 'max:50'],
            'birth_place' => ['nullable', 'string', 'max:100'],

            // Contato
            'personal_email' => ['nullable', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'size:2'],
            'zip_code' => ['nullable', 'string', 'max:10'],

            // Emergência
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:50'],

            // Bancário
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_code' => ['nullable', 'string', 'max:10'],
            'agency' => ['nullable', 'string', 'max:20'],
            'account' => ['nullable', 'string', 'max:20'],
            'account_type' => ['nullable', 'string', 'max:20'],
            'pix_key' => ['nullable', 'string', 'max:100'],

            // Fiscal
            'pis_pasep' => ['nullable', 'string', 'max:20'],
            'ctps' => ['nullable', 'string', 'max:20'],
            'ctps_serie' => ['nullable', 'string', 'max:20'],
            'voter_id' => ['nullable', 'string', 'max:20'],
            'military_id' => ['nullable', 'string', 'max:20'],

            // Documentos/Dependentes
            'photo_url' => ['nullable', 'string', 'max:255'],
            'has_dependents' => ['sometimes', 'boolean'],
            'dependents_info' => ['nullable'], // normaliza no prepareForValidation

            // Educação
            'education_level' => ['nullable', Rule::in([
                'elementary','high_school','technical','bachelor','postgraduate','master','doctorate'
            ])],
            'institution' => ['nullable', 'string', 'max:100'],
            'course' => ['nullable', 'string', 'max:100'],
            'graduation_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],

            // Avaliações
            'last_evaluation_date' => ['nullable', 'date'],
            'last_evaluation_score' => ['nullable', 'numeric', 'min:0', 'max:9.99'],
            'evaluation_comments' => ['nullable', 'string'],

            // Extras JSON
            'skills' => ['nullable'],
            'certifications' => ['nullable'],
            'languages' => ['nullable'],
            'metadata' => ['nullable'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // checkboxes: quando não vem, precisa virar false
            'has_dependents' => $this->boolean('has_dependents'),
            // normaliza json fields (string JSON -> array/null)
            'dependents_info' => $this->normalizeJson($this->input('dependents_info')),
            'skills' => $this->normalizeJson($this->input('skills')),
            'certifications' => $this->normalizeJson($this->input('certifications')),
            'languages' => $this->normalizeJson($this->input('languages')),
            'metadata' => $this->normalizeJson($this->input('metadata')),
        ]);
    }

    private function normalizeJson(mixed $value): mixed
    {
        if ($value === null || $value === '') return null;
        if (is_array($value)) return $value;

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value; // mantém string se não for JSON válido
        }

        return $value;
    }
}
