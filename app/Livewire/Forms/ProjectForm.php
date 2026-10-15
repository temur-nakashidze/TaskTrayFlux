<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class ProjectForm extends Form
{
    #[Validate('required|min:2')]
    public string $name = '';

    public string $color = '';

    public string $template = '';
}
