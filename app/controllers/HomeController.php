<?php

declare(strict_types=1);

namespace App\Controllers;

use Dasintranet\Framework\Controller;
use Dasintranet\Framework\View;

class HomeController extends Controller
{
    public static function index(): string
    {
        return (new View())->render('index');
    }

    public static function helloRender(): string
    {
        return (new View())->render('hello', [
            'titulo'    => 'Hola desde DasIntranet',
            'mensaje'   => 'Página utilizando layouts.'
        ]);
    }

    public static function helloExtend(): string
    {
        return (new View())->extend('hello2', [
            'titulo'    => 'Hola desde DasIntranet',
            'mensaje'   => 'Página NO utilizando layouts.'
        ]);
    }
}
