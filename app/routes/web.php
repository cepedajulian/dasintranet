<?php
use Dasintranet\Framework\App;
use App\Controllers\HomeController;

App::get('/hello', [HomeController::class, 'hello']);