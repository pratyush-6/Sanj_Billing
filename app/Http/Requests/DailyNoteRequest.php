<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DailyNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:5000'],
            'time' => ['nullable', 'date_format:H:i'],
            'category' => ['nullable', 'string', Rule::in(config('calendar.note_categories'))],
        ];
    }
}
