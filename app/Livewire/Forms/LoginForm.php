<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use App\Models\User;

class LoginForm extends Form
{
    #[Rule('required', 'email')]
    public string $email = "";

    #[Rule('required', 'min:6')]
    public string $password = "";

    public function store(): void
    {
        $credentials = $this->validate();

        // Cek apakah user ada
        $user = User::where('email', $credentials['email'])->first();
        if (! $user) {
            throw ValidationException::withMessages([
                'form.email' => 'User tidak ditemukan.',
            ]);
        }

        // Cek password
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'form.password' => 'Password salah.',
            ]);
        }

        // Login berhasil
    }
}
