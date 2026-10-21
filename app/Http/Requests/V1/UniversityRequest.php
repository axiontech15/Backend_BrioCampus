<?php

namespace App\Http\Requests\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UniversityRequest extends FormRequest
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
            'agent_id' => ['required','integer','exists:users,id'],
            'name' => ['required','string','max:255'],
            'logo' => ['nullable','string','max:255'],
            'address' => ['required','string','max:255'],
            'status' => ['required','string','max:255','in:active,inactive,pending'],
        ];
    }
}
