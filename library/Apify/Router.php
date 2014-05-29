<?php
/**
 * Zend Framework
 *
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd New BSD License
 */
class Apify_Router
{
    /**
     * @var array Array of routes to match against
     */
    protected $routes = array();
    
    /**
     * Add routes to the route chain
     *
     * @param array $routes Array of routes with names as keys and routes as values
     */
    public function addRoutes($routes) 
    {
        foreach ($routes as $name => $route) {
            $this->routes[$name] = $route;
        }
    }

    /**
     * Check if named route exists
     *
     * @param string $name Name of the route
     * @return boolean
     */
    public function hasRoute($name)
    {
        return isset($this->_routes[$name]);
    }
    
    /**
     * Retrieve a named route
     *
     * @param string $name Name of the route
     * @return Apify_Route object
     * @throws RuntimeException
     */
    public function getRoute($name)
    {
        if (!isset($this->routes[$name])) {
            throw new RuntimeException("Route $name is not defined");
        }
        return $this->routes[$name];
    }

    /**
     * Retrieve an array of routes added to the route chain
     *
     * @return array All of the defined routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Find a matching route to the current url path and inject
     * returning values to the Request object.
     *
     * @return Apify_Request object
     */
    public function route(Apify_Request $request)
    {
        if (! $this->hasRoute('default')) {
            $route = array('controller' => 'index', 'action' => 'index');
            $compat = new Apify_RouteStatic('default', $route);
            $this->routes = array_merge(array('default' => $compat), $this->routes);
        }
        
        $urlPath = $request->getUrlPath();

        foreach (array_reverse($this->routes) as $name => $route) {
            if ($params = $route->match($urlPath)) {
                $this->setRequestParams($request, $params);
                break;
            }
        }

        return $request;
    }

    public function setRequestParams($request, $params)
    {
        foreach ($params as $param => $value) {
            $request->setParam($param, $value);
            if ($param === 'controller') {
                $request->setController($value);
            }
            if ($param === 'action') {
                $request->setAction($value);
            }
        }
    }
}
