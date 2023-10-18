<?php 
require_once 'app/vendor/autoload.php'; 

use Classes\{
    Router,
    DB,
};

$db = new DB();
$router = new Router();

//Project settings
// $router->setErrorRedirect("/");
$router->setErrorPage("test.php");
$router->setProjectName("MyPHPProject");
$router->setFavicon("/favicon.ico");
$router->addMeta('name="theme-color" content="black"');

//Styles
$router->addStyle("styles.css");

//Scripts
$router->addScript("scripts.js");

$router->addRoute('*', '/', function () use ($router, $db) {
    $router->build();
    $router->getSite("test.php");
    // $w = $db->sql2array_file("test.sql");
});

$router->addRoute('*', '/user/:userid', function ($userid) {
    echo $userid;
})->where('userid', '/^\d+$/');
?>