<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDatabaseRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'database' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'backup_frequency' => ['required', 'in:hourly,daily,weekly,custom'],
            'custom_backup_interval_minutes' => ['required_if:backup_frequency,custom', 'integer', 'min:1'],
            'destination_type' => ['required', 'in:local'],
            'destination_path' => ['required', 'string', 'max:255'],
            'destination_username' => ['nullable', 'string', 'max:255'],
            'destination_password' => ['nullable', 'string', 'max:255'],
        ];
    }
}
