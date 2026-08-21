<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterCharacterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:Alive,Dead,unknown'],
            'species' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'in:Female,Male,Genderless,unknown'],
        ];
    }
}
