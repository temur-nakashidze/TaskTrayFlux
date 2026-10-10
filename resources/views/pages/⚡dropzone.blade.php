<?php

use App\Concerns\Ui\WithHeader;
use Livewire\Component;

new class extends Component
{
    use WithHeader;
};
?>

<div>

    {{ $this->header(['title' => 'Dropzone']) }}
    {{-- Live as if you were to die tomorrow. Learn as if you were to live forever. - Mahatma Gandhi --}}
</div>
