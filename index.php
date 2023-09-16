<?php 
require_once 'shared/vendor/autoload.php'; 

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
$router->addStyle("shared/web/css/styles.css");

//Scripts
$router->addScript("shared/web/js/styles.js");

$router->addRoute('*', '/', function () use ($router) {
    $router->build();
});

?>