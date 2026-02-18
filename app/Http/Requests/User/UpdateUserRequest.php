<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->id === $this->route('user')->id || $user->isAdmin());
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $isAdmin = $this->user()->isAdmin();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ];

        if ($isAdmin) {
            $rules['role'] = ['required', 'in:participant,jury,admin'];
        }

        if ($this->filled('password')) {
            $rules['password'] = ['string', 'confirmed', Password::min(8)];
            $rules['current_password'] = ['required_with:password', 'string'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Имя обязательно для заполнения',
            'email.required' => 'Email обязателен для заполнения',
            'email.unique' => 'Пользователь с таким email уже существует',
            'current_password.required_with' => 'Для смены пароля укажите текущий пароль',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('password')) {
                if (!\Hash::check($this->current_password, $this->route('user')->password)) {
                    $validator->errors()->add('current_password', 'Текущий пароль неверен');
                }
            }
        });
    }
}
