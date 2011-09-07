<?php
$routes[] = new Route('/*', 
    array(
        'controller' => 'index', 
        'action'     => 'index'
    )
);

$routes[] = new Route('/apify/:action', 
    array(
        'controller' => 'index'
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
        'controller' => 'index',
        'action'     => 'show'
    ),
    array(
        'page'       => '\d+'
    )
);

return $routes;
