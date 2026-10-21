<?php

namespace App\Http\Requests\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PalmaresRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'university_id' => ['required','integer','exists:universities,id'],
            'file' => ['required','file','mimes:pdf,xls,xlsx,csv','max:2048'],
            'uploaded_by' => ['required','integer','exists:users,id'],
            'status' => ['required','string','max:255','in:pending,approved,rejected'],
            'uploaded_at' => ['nullable','date'],
            'verified_at' => ['nullable','date'],
            'verified_by' => ['nullable','integer','exists:users,id'],
        ];
    }
}
