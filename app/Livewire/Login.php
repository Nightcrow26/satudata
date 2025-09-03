<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule;
use App\Livewire\Forms\LoginForm;

#[Title('Login')]

#[Layout('components.layouts.guest')]
class Login extends Component
{
    public LoginForm $form;

    public function login(){

      $this->form->store();
      return redirect()->route('dashboard');

    }

    public function render()
    {
        return view('livewire.login');
    }
}
