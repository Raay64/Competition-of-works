<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'email_on_status_change' => ['sometimes', 'boolean'],
            'email_on_comments' => ['sometimes', 'boolean'],
            'email_on_deadline' => ['sometimes', 'boolean'],
            'push_notifications' => ['sometimes', 'boolean'],
            'digest_frequency' => ['sometimes', 'in:never,daily,weekly,monthly'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email_on_status_change' => $this->boolean('email_on_status_change'),
            'email_on_comments' => $this->boolean('email_on_comments'),
            'email_on_deadline' => $this->boolean('email_on_deadline'),
            'push_notifications' => $this->boolean('push_notifications'),
        ]);
    }
}
