<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use App\Controller\GraphQL;

$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    $r->post('/graphql', [GraphQL::class, 'handle']);
});

$routeInfo = $dispatcher->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);

switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        // 404 Not Found
        header('HTTP/1.1 404 Not Found');
        echo '404 Not Found';
        break;
    case Dispatcher::METHOD_NOT_ALLOWED:
        // 405 Method Not Allowed
        header('HTTP/1.1 405 Method Not Allowed');
        echo '405 Method Not Allowed';
        break;
    case Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        
        // Instantiate the GraphQL class and call the handle method
        $graphQL = new GraphQL();
        $graphQL->handle();
        break;
}
