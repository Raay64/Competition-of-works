<?php

namespace App\Http\Requests\Submission;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $submission = $this->route('submission');
        return $this->user() &&
            $this->user()->id === $submission->user_id &&
            $submission->canBeEdited();
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
            'file.max' => 'Файл не может превышать 10 МБ',
            'file.mimes' => 'Допустимые форматы: PDF, ZIP, PNG, JPG, JPEG',
        ];
    }
}
