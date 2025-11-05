<?php

namespace App\Controllers;

use App\Views\View;

class PackageController
{
    public function show(): void
    {
        $view = new View('package');
        $view->render([
            'title' => 'Detalle del tour — Expediatravels',
        ]);
    }
}
