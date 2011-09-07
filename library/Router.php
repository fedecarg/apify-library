<?php
/**
 * Zend Framework
 *
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd New BSD License
 */
class Router
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
     * @return Route object
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
     * @return Request object
     */
    public function route(Request $request)
    {
        if (! $this->hasRoute('default')) {
            $route = array('controller' => 'index', 'action' => 'index');
            $compat = new RouteStatic('default', $route);
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

/**
 * Zend Framework
 *
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd New BSD License
 */
class Route
{
    protected $urlVariable = ':';
    protected $urlDelimiter = '/';
    protected $regexDelimiter = '#';
    protected $defaultRegex = null;

    protected $parts;
    protected $defaults = array();
    protected $requirements = array();
    protected $staticCount = 0;
    protected $vars = array();
    protected $params = array();
    protected $values = array();

    /**
     * Prepares the route for mapping by splitting (exploding) it
     * to a corresponding atomic parts. These parts are assigned
     * a position which is later used for matching and preparing values.
     *
     * @param string $route Map used to match with later submitted URL path
     * @param array $defaults Defaults for map variables with keys as variable names
     * @param array $reqs Regular expression requirements for variables (keys as variable names)
     */
    public function __construct($route, $defaults = array(), $reqs = array())
    {
        $route = trim($route, $this->urlDelimiter);
        $this->defaults = (array) $defaults;
        $this->requirements = (array) $reqs;

        if ($route != '') {

            foreach (explode($this->urlDelimiter, $route) as $pos => $part) {
                if (substr($part, 0, 1) == $this->urlVariable) {
                    $name = substr($part, 1);
                    $regex = (isset($reqs[$name]) ? $reqs[$name] : $this->defaultRegex);
                    $this->parts[$pos] = array('name' => $name, 'regex' => $regex);
                    $this->vars[] = $name;
                } else {
                    $this->parts[$pos] = array('regex' => $part);
                    if ($part != '*') {
                        $this->staticCount++;
                    }
                }
            }
        }
    }
    
    protected function getWildcardData($parts, $unique)
    {
        $pos = count($parts);
        if ($pos % 2) {
            $parts[] = null;
        }
        foreach(array_chunk($parts, 2) as $part) {
            list($var, $value) = $part;
            $var = urldecode($var);
            if (!array_key_exists($var, $unique)) {
                $this->params[$var] = urldecode($value);
                $unique[$var] = true;
            }
        }
    }

    /**
     * Matches a user submitted path with parts defined by a map. Assigns and
     * returns an array of variables on a successful match.
     *
     * @param string $path Path used to match against this routing map
     * @return array|false An array of assigned values or a false on a mismatch
     */
    public function match($path)
    {
        $pathStaticCount = 0;
        $defaults = $this->defaults;

        if (count($defaults)) {
            $unique = array_combine(array_keys($defaults), array_fill(0, count($defaults), true));
        } else {
            $unique = array();
        }

        $path = trim($path, $this->urlDelimiter);

        if ($path != '') {

            $path = explode($this->urlDelimiter, $path);

            foreach ($path as $pos => $pathPart) {

                if (!isset($this->parts[$pos])) {
                    return false;
                }

                if ($this->parts[$pos]['regex'] == '*') {
                    $parts = array_slice($path, $pos);
                    $this->getWildcardData($parts, $unique);
                    break;
                }

                $part = $this->parts[$pos];
                $name = isset($part['name']) ? $part['name'] : null;
                $pathPart = urldecode($pathPart);

                if ($name === null) {
                    if ($part['regex'] != $pathPart) {
                        return false;
                    }
                } elseif ($part['regex'] === null) {
                    if (strlen($pathPart) == 0) {
                        return false;
                    }
                } else {
                    $regex = $this->regexDelimiter . '^' . $part['regex'] . '$' . $this->regexDelimiter . 'iu';
                    if (!preg_match($regex, $pathPart)) {
                        return false;
                    }
                }

                if ($name !== null) {
                    // It's a variable. Setting a value
                    $this->values[$name] = $pathPart;
                    $unique[$name] = true;
                } else {
                    $pathStaticCount++;
                }

            }

        }

        $return = $this->values + $this->params + $this->defaults;

        // Check if all static mappings have been met
        if ($this->staticCount != $pathStaticCount) {
            return false;
        }

        // Check if all map variables have been initialized
        foreach ($this->vars as $var) {
            if (!array_key_exists($var, $return)) {
                return false;
            }
        }

        return $return;
    }

    /**
     * Assembles user submitted parameters forming a URL path defined by this route
     *
     * @param  array $data An array of variable and value pairs used as parameters
     * @param  boolean $reset Whether or not to set route defaults with those provided in $data
     * @return string Route path with user submitted parameters
     * @throws RuntimeException
     */
    public function assemble($data = array(), $reset = false)
    {
        $url = array();
        $flag = false;

        foreach ($this->parts as $key => $part) {

            $resetPart = false;
            if (isset($part['name']) && array_key_exists($part['name'], $data) && $data[$part['name']] === null) {
                $resetPart = true;
            }

            if (isset($part['name'])) {

                if (isset($data[$part['name']]) && !$resetPart) {
                    $url[$key] = $data[$part['name']];
                    unset($data[$part['name']]);
                } elseif (!$reset && !$resetPart && isset($this->values[$part['name']])) {
                    $url[$key] = $this->values[$part['name']];
                } elseif (!$reset && !$resetPart && isset($this->params[$part['name']])) {
                    $url[$key] = $this->params[$part['name']];
                } elseif (isset($this->defaults[$part['name']])) {
                    $url[$key] = $this->defaults[$part['name']];
                } else {
                    throw new RuntimeException($part['name'] . ' is not specified');
                }

            } else {

                if ($part['regex'] != '*') {
                    $url[$key] = $part['regex'];
                } else {
                    if (!$reset) $data += $this->params;
                    foreach ($data as $var => $value) {
                        if ($value !== null) {
                            $url[$var] = $var . $this->urlDelimiter . $value;
                            $flag = true;
                        }
                    }
                }
            }
        }

        $return = '';
        foreach (array_reverse($url, true) as $key => $value) {
            if ($flag || !isset($this->parts[$key]['name']) || $value !== $this->getDefault($this->parts[$key]['name'])) {
                $return = $this->urlDelimiter . $value . $return;
                $flag = true;
            }
        }

        return trim($return, $this->urlDelimiter);

    }

    /**
     * Return a single parameter of route's defaults
     *
     * @param string $name Array key of the parameter
     * @return string Previously set default
     */
    public function getDefault($name) 
    {
        if (isset($this->defaults[$name])) {
            return $this->defaults[$name];
        }
        return null;
    }

    /**
     * Return an array of defaults
     *
     * @return array Route defaults
     */
    public function getDefaults() 
    {
        return $this->defaults;
    }
}

/**
 * Zend Framework
 *
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd New BSD License
 */
class RouteStatic
{
    protected $route = null;
    protected $defaults = array();

    /**
     * Prepares the route for mapping.
     *
     * @param string $route Map used to match with later submitted URL path
     * @param array $defaults Defaults for map variables with keys as variable names
     */
    public function __construct($route, $defaults = array())
    {
        $this->route = trim($route, '/');
        $this->defaults = (array) $defaults;
    }

    /**
     * Matches a user submitted path with a previously defined route.
     * Assigns and returns an array of defaults on a successful match.
     *
     * @param string $path Path used to match against this routing map
     * @return array|false An array of assigned values or a false on a mismatch
     */
    public function match($path)
    {
        if (trim($path, '/') == $this->route) {
            return $this->defaults;
        }
        return false;
    }

    /**
     * Assembles a URL path defined by this route
     *
     * @param array $data An array of variable and value pairs used as parameters
     * @return string Route path with user submitted parameters
     */
    public function assemble($data = array())
    {
        return $this->route;
    }

    /**
     * Return a single parameter of route's defaults
     *
     * @param string $name Array key of the parameter
     * @return string Previously set default
     */
    public function getDefault($name) 
    {
        if (isset($this->defaults[$name])) {
            return $this->defaults[$name];
        }
        return null;
    }

    /**
     * Return an array of defaults
     *
     * @return array Route defaults
     */
    public function getDefaults() 
    {
        return $this->defaults;
    }
}

