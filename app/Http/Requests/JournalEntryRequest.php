<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $websiteId = $this->route('journal')?->website_client_id ?? $this->input('website_client_id');
        $date = $this->input('entry_date');

        return [
            'website_client_id' => 'required|exists:website_clients,id',
            'entry_date' => [
                'required',
                'date',
                // Unique per website per date, exclude current record on update
                \Illuminate\Validation\Rule::unique('journal_entries')
                    ->where('website_client_id', $websiteId)
                    ->where('entry_date', $date)
                    ->ignore($this->route('journal')),
            ],
            'activities' => 'required|array|min:1',
            'activities.*.type' => 'required|string|in:wp_update,plugin_update,theme_update,article,page_optimization,other',
            'summary' => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'activities.required' => 'Minimal satu aktivitas harus diisi.',
            'activities.*.type.in' => 'Tipe aktivitas tidak valid.',
            'entry_date.unique' => 'Sudah ada entry jurnal untuk website dan tanggal ini.',
        ];
    }
}
