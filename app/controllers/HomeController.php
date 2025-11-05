<?php

namespace App\Controllers;

use App\Views\View;

class HomeController
{
    public function index(): void
    {
        $view = new View('home');
        $view->render([
            'title' => 'Expediatravels — Explora Oxapampa',
        ]);
    }
}
