<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WebsiteClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|exists:customers,id',
            'name' => 'required|string|max:255',
            'url' => 'required|string|max:500',
            'wp_version' => 'nullable|string|max:20',
            'theme_name' => 'nullable|string|max:255',
            'theme_version' => 'nullable|string|max:20',
            'plugins' => 'nullable|array',
            'plugins.*.name' => 'required|string|max:255',
            'plugins.*.version' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:5000',
            'is_active' => 'boolean',
        ];
    }
}
