<?php 
require_once 'shared/vendor/autoload.php'; 
use Classes\{
    Router,
    DB,
    Login,
};

if ($_SERVER['HTTP_HOST'] != "localhost") {
    ini_set("session.save_path","/var/www/vhosts/hosting180901.ae8a9.netcup.net/merida.callmeleon.de/httpdocs/sessions_bjyyyyyyyd");
}

$db = new DB();
$user = new Login();
// $user->register("CallMeLeon", "GermanFr3aksLP");
// // $user->login("CallMeLeon", "GermanFr3aksLP");
// exit;


$router = new Router(basename(__DIR__));

// $router->setErrorRedirect("/");
$router->setProjectName("Backend Version v1.1");
$router->setFavicon("/favicon.ico");
$router->disableComments();

$router->addRoute('*', '/', function () use ($router) {
    $router->setTitle("Home");
    $router->addStyle('/styles.css');
    $router->addScript('/scripts.js');
    $router->build();

    echo "<h1>Your on the index of this App</h1>";
})
->setAlias('/pe')
->setAlias('/pen');

$router->addRoute('*', '/api', function () use ($router, $db) {
    $router->isApi();

    $test = $db->sql2array_file("test.sql", [53, 55]);
    echo json_encode($test, JSON_PRETTY_PRINT);

});

$router->addRoute('POST', '/user/data', function () {
    //Get user data only via Ajax
})->onlyAjax();

$router->addRoute('GET', '/test', function () use ($router) {
    $router->build();

    $variables = ['page' => "/test", 'url' => $router->projectName];
    $router->getSite('test', $variables);
});

$router->addRoute('GET', '/test/:id', function () use ($router) {

    $router->useController("TestController", "getTest", ["controllerName" => "TestController"]);

    echo "test-id: " . $router->getRouteParam('test');
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

?>