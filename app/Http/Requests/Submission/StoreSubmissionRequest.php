<?php

namespace App\Http\Requests\Submission;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isParticipant();
    }

    public function rules(): array
    {
        return [
            'contest_id' => ['required', 'exists:contests,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'contest_id.required' => 'Выберите конкурс',
            'contest_id.exists' => 'Выбранный конкурс не существует',
            'title.required' => 'Название работы обязательно',
            'title.max' => 'Название не может превышать 255 символов',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $contest = \App\Models\Contest::find($this->contest_id);

            if ($contest) {
                if (!$contest->is_active) {
                    $validator->errors()->add('contest_id', 'Этот конкурс неактивен');
                }

                if ($contest->deadline_at <= now()) {
                    $validator->errors()->add('contest_id', 'Дедлайн этого конкурса уже прошел');
                }
            }
        });
    }
}
