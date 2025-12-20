<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'timezone' => ['required', 'string', 'timezone'],
            'theme' => ['required', 'in:light,dark'],
            'backup_filename_format' => ['required', 'string', 'max:255'],
            'active_tab' => ['nullable', 'string', 'in:timezone,appearance'],
        ];
    }
}
