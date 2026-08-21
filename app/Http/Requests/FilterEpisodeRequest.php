<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterEpisodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'episode' => ['nullable', 'string', 'max:10'],
        ];
    }
}
