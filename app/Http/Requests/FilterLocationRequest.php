<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'dimension' => ['nullable', 'string', 'max:255'],
        ];
    }
}
