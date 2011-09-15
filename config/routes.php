<?php
$routes[] = new Route('/', 
    array(
        'controller' => 'index', 
        'action'     => 'index'
    )
);

$routes[] = new Route('/example/:action', 
    array(
        'controller' => 'index'
    )
);

$routes[] = new Route('/users/:id', 
    array(
        'controller' => 'users',
        'action'     => 'show'
    )
);

$routes[] = new Route('/users/:id/:action', 
    array(
        'controller' => 'users'
    ),
    array(
        'action'     => '(show|update|destroy)',
        'page'       => '\d+'
    )
);

$routes[] = new Route('/users/create', 
    array(
        'controller' => 'users',
        'action'     => 'create'
    )
);

$routes[] = new Route('/users', 
    array(
        'controller' => 'users',
        'action'     => 'index'
    )
);

return $routes;
