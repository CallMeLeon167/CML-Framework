<?php
require_once 'app/admin/cml-load.php';

use CML\Classes\Router as App;
use CML\Classes\DB;

$db = new DB(false);
$app = new App();

//Project settings
$app->activateMinifyHTML();
$app->setErrorRedirect("/");
$app->setFavicon("favicon.ico");
$app->addMeta('name="theme-color" content="black"');

//Global CDNs
$app->addCDN("link", 'rel="preconnect" href="https://fonts.googleapis.com"');
$app->addCDN("link", 'rel="preconnect" href="https://fonts.gstatic.com" crossorigin');
$app->addCDN("link", 'href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;400;600&display=swap" rel="stylesheet"');

$app->addRoute('GET', '/', function () use ($app) {
    $app->addStyle($app->compress("styles.css"));
    $app->addScript($app->compress("scripts.js"));
    $app->addFooter();

    $apiData = $app->useController("ApiController", "getRepoData", ['url' => 'https://docs.callmeleon.de/api/data']);
    $app->setTitle("Thank you! | CML - Framework");
    $app->view("home.php", $apiData);
}, "home");
