<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use \App\Models\User;

class PostForm extends Form
{
    #[Rule('required')]
    public string $body ="";

    public function store(){
       $post =  Auth::user()->posts()->create(
            $this->validate()
        );
        
        flash('Post Created Successfully','success');

        $this->reset();

        return $post;
    }
}
