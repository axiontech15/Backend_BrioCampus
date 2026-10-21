<?php

namespace App\Http\Requests\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
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
            'user_id' => ['required','integer','exists:users,id'],
            'category_id' => ['required','integer','exists:categories,id'],
            'quantity' => ['required','integer','min:1'],
            'status' => ['required','string','max:255','in:pending,approved,rejected'],
            'closed_at' => ['nullable','date'],
        ];
    }
}
