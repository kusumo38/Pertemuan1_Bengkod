<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'password_lama' => ['required', 'current_password'],
            'password_baru' => ['required', 'string', 'min:8', 'different:password_lama', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'password_lama.current_password' => 'Password lama tidak sesuai dengan akun Anda.',
            'password_baru.different' => 'Password baru harus berbeda dari password lama.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ];
    }
}
