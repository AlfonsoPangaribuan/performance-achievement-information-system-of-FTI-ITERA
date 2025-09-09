<?php

namespace App\Http\Requests\IndikatorKinerjaProgram;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\RequestErrorMessage;

class EditRequest extends FormRequest
{
    use RequestErrorMessage;

    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'number' => ['bail', 'required', 'numeric', 'integer', 'min:1', 'max_digits:10'],
            'definition' => ['bail', 'required', 'string', 'max:65000'],
            'name' => ['bail', 'required', 'string', 'max:65000'],
            'type' => ['bail', 'required', 'in:iku,ikt'],
            'assigned_to_type' => ['bail', 'required', 'in:admin,kk'],
            'unit_id' => ['bail', 'required_if:assigned_to_type,kk', 'array'],
            'unit_id.*' => ['bail', 'exists:units,id'],
        ];
    }

    /**
     * Aliases name
     * @return array
     */
    public function attributes(): array
    {
        return [
            'definition' => 'Definisi operasional',
            'name' => 'Program strategis',
            'type' => 'Tipe pendukung',
            'number' => 'Nomor',
            'assigned_to_type' => 'Tugaskan kepada',
            'unit_id' => 'Unit',
        ];
    }
}
