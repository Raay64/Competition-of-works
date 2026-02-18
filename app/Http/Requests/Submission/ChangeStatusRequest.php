<?php

namespace App\Http\Requests\Submission;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isJury() || $this->user()->isAdmin());
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:submitted,needs_fix,accepted,rejected'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Статус обязателен для выбора',
            'status.in' => 'Выбран недопустимый статус',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $submission = $this->route('submission');

            if (!$submission->canJurySetStatus($this->status)) {
                $validator->errors()->add('status', 'Недопустимый переход статуса');
            }
        });
    }
}
