<?php

namespace App\Http\Requests\IndikatorKinerja;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\RequestErrorMessage;
use App\Models\IndikatorKinerja;

class AddRequest extends FormRequest
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
            'type' => ['bail', 'required', 'in:' . implode(',', IndikatorKinerja::getTypeValues())],
            'number' => ['bail', 'required', 'numeric', 'integer', 'min:1', 'max_digits:10'],
            'name' => ['bail', 'required', 'string', 'max:65000'],
            'selection.*' => ['bail', 'string', 'max:255'],
            'selection' => ['bail', 'nullable', 'array'],
            'assigned_to_type' => ['bail', 'required', 'in:admin,kk'],
            'unit_id' => ['bail', 'required_if:assigned_to_type,kk', 'exists:units,id'],
        ];
    }

    /**
     * Aliases name
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => 'Indikator kinerja',
            'selection' => 'Pilihan',
            'type' => 'Tipe data',
            'number' => 'Nomor',
            'assigned_to_type' => 'Tugaskan kepada',
            'unit_id' => 'Unit',
        ];
    }
}
