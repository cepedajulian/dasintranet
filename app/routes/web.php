<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use Dasintranet\Framework\App;

App::get('/',            [HomeController::class, 'index']);
App::get('/helloRender', [HomeController::class, 'helloRender']);
App::get('/helloExtend', [HomeController::class, 'helloExtend']);