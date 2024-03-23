<?php 
require_once 'app/admin/cml-load.php'; 

use CML\Classes\Router;
use CML\Classes\DB;

$db = new DB();
$app = new Router();

//Project settings
$app->activateMinifyHTML();
$app->setErrorRedirect("/");
$app->setFavicon("favicon.ico");
$app->addMeta('name="theme-color" content="black"');

//Global CDNs
$app->addCDN("link", 'rel="preconnect" href="https://fonts.googleapis.com"');
$app->addCDN("link", 'rel="preconnect" href="https://fonts.gstatic.com" crossorigin');
$app->addCDN("link", 'href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;400;600&display=swap" rel="stylesheet"');

//Global Styles
$app->addStyle($app->compress("styles.css"));

//Global Scripts
$app->addScript($app->compress("scripts.js"));

//Global HTML
$app->addFooter();

$app->addRoute('GET', '/', function () use ($app) {
    $apiData = $app->useController("ApiController", "getRepoData", ['url' => 'https://docs.callmeleon.de/data']);
    $app->setTitle("Thank you! | CML - Framework");
    $app->view("home.php", $apiData);
}, "home");