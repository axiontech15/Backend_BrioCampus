<?php

namespace App\Http\Requests\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PalmaresRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isStore = $this->isMethod('POST');

        return [
            'university_id' => [$isStore ? 'required' : 'sometimes', 'integer', 'exists:universities,id'],
            'file' => [
                Rule::requiredIf($isStore),
                'file',
                'mimes:csv,txt,xlsx,xls',
                'max:5120',
            ],
            'uploaded_by' => [$isStore ? 'required' : 'sometimes', 'integer', 'exists:users,id'],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'approved', 'rejected'])],
            'uploaded_at' => ['nullable', 'date'],
            'verified_at' => ['nullable', 'date'],
            'verified_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Le fichier palmarès est obligatoire.',
            'file.mimes' => 'Formats acceptés : CSV ou Excel (.xlsx).',
            'university_id.required' => 'L’université est obligatoire.',
            'uploaded_by.required' => 'L’utilisateur qui importe est obligatoire.',
        ];
    }
}
