<?php
namespace App\Controllers;

use Dasintranet\Framework\Controller;
use Dasintranet\Framework\View;
//use Dasintranet\Framework\Db;
//use Dasintranet\Framework\Api;

class HomeController extends Controller{

	public static function hello(){
		$view = new View();
		return $view->render("hello");
	}
}

require_once(__DIR__.'/functions.php');