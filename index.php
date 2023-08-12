<?php 
require_once 'shared/vendor/autoload.php'; 
use Classes\Router;

$router = new Router(basename(__DIR__));
$router->setErrorRedirect("/");
$router->setProjectTitle("Was geht");

$router->addRoute('*', '/', function () use ($router) {
    $router->setTitle("Home");
    $router->addStyle('/styles.css');
    $router->addScript('/scripts.js');
    $router->build();

    echo "<h1>Your on the index of this App</h1>";
});

$router->addRoute('*', '/api', function () use ($router) {
    $router->isApi();
    echo json_encode(["penis" => "penis"]);
});

$router->addRoute('GET', '/test', function () use ($router) {
    $router->build();

    $variables = ['page' => "/test", 'url' => $router->projectName];
    $router->getSite('test', $variables);
});

$router->addGroup('/admin', function($router, $prefix) {
    $router->addRoute('GET', $prefix . '', function () {
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