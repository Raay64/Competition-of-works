<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $comment = $this->route('comment');
        $user = $this->user();

        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $comment->user_id && $comment->created_at->diffInHours(now()) <= 1;
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
        ];
    }
}
