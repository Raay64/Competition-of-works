<?php

namespace App\Http\Requests\Attachment;

use Illuminate\Foundation\Http\FormRequest;

class UploadAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $submission = $this->route('submission');
        $user = $this->user();

        if (!$user || $user->id !== $submission->user_id) {
            return false;
        }

        if (!$submission->canBeEdited()) {
            return false;
        }

        // Проверка лимита файлов (максимум 3)
        if ($submission->attachments()->count() >= 3) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,zip,png,jpg,jpeg'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Выберите файл для загрузки',
            'file.max' => 'Размер файла не может превышать 10 МБ',
            'file.mimes' => 'Допустимые типы файлов: PDF, ZIP, PNG, JPG, JPEG',
        ];
    }
}
