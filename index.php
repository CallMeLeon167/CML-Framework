<?php 
require_once 'shared/vendor/autoload.php'; 
use Classes\{
    Router,
    DB
};

$db = new DB();
$router = new Router(basename(__DIR__));

// $router->setErrorRedirect("/");
$router->setProjectName("Backend Version v1");

$router->addGlobalMiddleware(["/login", "/api"], function () {
    echo "<h1>GLOBAL ist gesetzt!!!!!!!!</h1>";
});

$router->addRoute('*', '/', function () use ($router, $db) {
    $router->setTitle("Home");
    $router->addStyle('/styles.css');
    $router->addScript('/scripts.js');
    $router->build();

    echo "<h1>Your on the index of this App</h1>";
})->addMiddleware(function () {
    echo "Willkommen ";
})
->setAlias('/pe')
->setAlias('/pen');

$router->addRoute('*', '/api', function () use ($router) {
    $router->isApi();
    echo json_encode(["penis" => "penis"]);
    });

$router->addRoute('POST', '/user/data', function () {
    //Get user data only via Ajax
})->onlyAjax();

$router->addRoute('GET', '/test', function () use ($router) {
    $router->build();

    $variables = ['page' => "/test", 'url' => $router->projectName];
    $router->getSite('test', $variables);
});

$router->addGroup('/admin', function($router, $prefix) {
    $router->addRoute('GET', $prefix . '/dashboard', function () {
        echo "Admin Dashboard";
    });
    $router->addRoute('GET', $prefix . '/users', function () {
        echo "Admin Users";
    });
})->addMiddleware(function () {
    echo "ADMIN MIDDLeWARE ";
});

$router->matchRoute();  
?>