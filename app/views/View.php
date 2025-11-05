<?php

namespace App\Views;

class View
{
    public function __construct(private string $template)
    {
    }

    public function render(array $data = []): void
    {
        extract($data);
        include __DIR__ . '/' . $this->template . '.php';
    }
}
