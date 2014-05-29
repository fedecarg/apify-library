<?php
$routes[] = new Apify_Route('/', 
    array(
        'controller' => 'index', 
        'action'     => 'index'
    )
);

$routes[] = new Apify_Route('/example/:action', 
    array(
        'controller' => 'index'
    )
);

$routes[] = new Apify_Route('/users/:id', 
    array(
        'controller' => 'users',
        'action'     => 'show'
    )
);

$routes[] = new Apify_Route('/users/:id/:action', 
    array(
        'controller' => 'users'
    ),
    array(
        'action'     => '(show|update|destroy)',
        'page'       => '\d+'
    )
);

$routes[] = new Apify_Route('/users/create', 
    array(
        'controller' => 'users',
        'action'     => 'create'
    )
);

$routes[] = new Apify_Route('/users', 
    array(
        'controller' => 'users',
        'action'     => 'index'
    )
);

return $routes;
