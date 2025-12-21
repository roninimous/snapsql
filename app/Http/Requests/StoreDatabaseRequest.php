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
            'backup_start_time' => ['required', 'date_format:H:i'],
            'custom_backup_interval_minutes' => ['nullable', 'required_if:backup_frequency,custom', 'integer', 'min:1'],
            'destination_type' => ['nullable', 'in:local,s3'],
            'destination_path' => ['required', 'string', 'max:255'],
            'r2_account_id' => ['nullable', 'string', 'max:255'],
            'r2_access_key_id' => ['nullable', 'string', 'max:255'],
            'r2_secret_access_key' => ['nullable', 'string', 'max:255'],
            'r2_bucket_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
