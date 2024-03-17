<?php 
require_once 'app/admin/cml-load.php'; 

use CML\Classes\Router;
use CML\Classes\DB;

$db = new DB();
$router = new Router();

//Project settings
$router->activateMinifyHTML();
$router->setErrorRedirect("/");
$router->setFavicon("favicon.ico");
$router->addMeta('name="theme-color" content="black"');

//Global CDNs
$router->addCDN("link", 'rel="preconnect" href="https://fonts.googleapis.com"');
$router->addCDN("link", 'rel="preconnect" href="https://fonts.gstatic.com" crossorigin');
$router->addCDN("link", 'href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;400;600&display=swap" rel="stylesheet"');

//Global Styles
$router->addStyle($router->compress("styles.css"));

//Global Scripts
$router->addScript($router->compress("scripts.js"));

//Global HTML
$router->addFooter();

$router->addRoute('GET', '/', function () use ($router) {
    $apiData = $router->useController("ApiController", "getRepoData", ['url' => 'https://docs.callmeleon.de/data']);
    $router->setTitle("Thank you! | CML - Framework");
    $router->view("home.php", $apiData);
}, "home");