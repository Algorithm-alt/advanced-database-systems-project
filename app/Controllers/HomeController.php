<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

final class HomeController
{
    public static function index(): void
    {
        if (Auth::user() !== null) {
            Response::redirect(Request::url('dashboard'));
        }

        View::render('home/index', [
            'title' => 'Retail operations, connected - Algorithm Hardware Plug',
        ]);
    }
}
