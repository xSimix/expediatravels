<?php

namespace App\Controllers;

use App\Views\View;

class ExploreController
{
    public function index(): void
    {
        $view = new View('explore');
        $view->render([
            'title' => 'Explorar experiencias — Expediatravels',
        ]);
    }
}
