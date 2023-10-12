<?php 
require_once 'app/vendor/autoload.php'; 

use Classes\{
    Router,
    DB,
};

$db = new DB();
$router = new Router();

//Project settings
$router->setErrorRedirect("/");
$router->setProjectName("MyPHPProject");
$router->setFavicon("/favicon.ico");
$router->addMeta('name="theme-color" content="black"');

//Styles
$router->addStyle("app/web/css/styles.css");

//Scripts
$router->addScript("app/web/js/styles.js");

$router->addRoute('*', '/', function () use ($router, $db) {
    $router->build();
});

?>