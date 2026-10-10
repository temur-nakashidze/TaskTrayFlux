<?php

namespace App\Concerns\Ui;

use Illuminate\View\View;

trait WithHeader
{
    public string $title = 'Project Name';

    public string $icon = 'user';

    public string $color = 'neutral';

    public ?bool $isWorkbench = false;

    public ?string $button = null;

    public ?string $buttonIcon = null;

    public bool $editable = true;

    public function header(array $config = []): View
    {
        return view(
            'components.page-header',
            array_merge(
                [
                    'title' => $this->title,
                    'icon' => $this->icon,
                    'isWorkbench' => $this->isWorkbench,
                    'editable' => $this->editable,
                    'color' => $this->color,
                    'button' => $this->button,
                    'buttonIcon' => $this->buttonIcon,
                ],
                $config,
            ),
        );
    }
}
