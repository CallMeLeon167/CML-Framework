<?php 
require_once 'shared/vendor/autoload.php'; 
use Classes\HTMLBuilder;
use Classes\Router;

$router = new Router(basename(__DIR__));

$router->setErrorRedirect("/");

$router->addRoute('*', '/', function () use ($router) {
    echo "<h1>Your on the index of this App</h1>";
    // $router->setPageTitle("etst");
    include "shared/classes/Datenbank.php";
});

$router->addRoute('GET', '/test', function () use ($router) {
    $variables = ['page' => "/test", 'url' => $router->projectName];
    $router->getSite('test', $variables);
});

$router->addGroup('/admin', function($router, $prefix) {
    $router->addRoute('GET', $prefix . '/', function () {
        echo "Admin Home";
    });
    $router->addRoute('GET', $prefix . '/dashboard', function () {
        echo "Admin Dashboard";
    });
    $router->addRoute('GET', $prefix . '/users', function () {
        echo "Admin Users";
    });
});

$router->matchRoute();  
?>