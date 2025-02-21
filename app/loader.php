<?php
//Load All Controllers
$files = glob( __DIR__ . '/controllers/' . '*.php');
foreach ($files as $file) {
    if(substr($file, -14)=='Controller.php' ){
        require_once $file;
    }
} 

//Load All RouterFiles
use Dasintranet\Framework\App;

$files = glob( __DIR__ . '/routes/' . '*.php');
foreach ($files as $file) {
    require_once $file;
}

App::run();
