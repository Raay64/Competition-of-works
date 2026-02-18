<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $submission = $this->route('submission');
        $user = $this->user();

        if (!$user) {
            return false;
        }

        if ($user->isParticipant()) {
            return $submission->user_id === $user->id;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Текст комментария обязателен',
            'body.max' => 'Комментарий не может превышать 5000 символов',
        ];
    }
}
