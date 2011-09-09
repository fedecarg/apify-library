<?php
/**
 * Apify - Copyright (c) 2011, Kewnode Ltd. All rights reserved.
 * 
 * THIS COPYRIGHT INFORMATION MUST REMAIN INTACT AND MAY NOT BE MODIFIED IN ANY WAY.
 * 
 * THIS SOFTWARE IS PROVIDED BY KEWNODE LTD "AS IS" AND ANY EXPRESS OR IMPLIED 
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO 
 * EVENT SHALL KEWNODE LTD BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, 
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT 
 * OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, 
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY 
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Library
 * @package     Request
 * @author      Federico Cargnelutti <federico@kewnode.com>
 * @copyright   Copyright (c) 2011 Kewnode Ltd.
 * @version     $Id: $
 */
class Request
{
    const CONTROLLER_INDEX  = 'Index';
    
    const ACTION_INDEX      = 'index';
    const ACTION_CREATE     = 'create';
    const ACTION_SHOW       = 'show';
    const ACTION_UPDATE     = 'update';
    const ACTION_DESTROY    = 'destroy';
        
    const METHOD_POST       = 'POST';
    const METHOD_GET        = 'GET';
    const METHOD_PUT        = 'PUT';
    const METHOD_DELETE     = 'DELETE';
    
    /**
     * @var boolean
     */
    protected $isUrlRewritingEnabled = false;
    
    /**
     * @var boolean
     */
    protected $isRestfulMappingEnabled = false;
        
    /**
     * @var null|Router
     */
    protected $router;
    
    /**
     * @var null|string
     */
    protected $urlKeyword;
    
    /**
     * null: auto-detect keyword
     *    0: keyword is in domain name
     *    1: keyword is in the first segment of the URL path
     *    2: keyword is in the second segment of the URL path
     * 
     * @var null|int
     */
    protected $urlSegment;
    
    /**
     * @var null|string
     */
    protected $urlPath;
    
    /**
     * @var null|array
     */
    protected $urlParts;
    
    /**
     * @var string
     */
    protected $controller = self::CONTROLLER_INDEX;
    
    /**
     * @var string
     */
    protected $action = self::ACTION_INDEX;
    
    /**
     * @var array
     */
    protected $params = array();
    
    
    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->setParams(array_merge($_GET, $_POST));
    }
    
    /**
     * @params string $keyword
     * @params int $segment
     * @return Request
     */
    public function setUrlKeyword($keyword, $segment = null)
    {
        $this->urlKeyword = $keyword;
        if (is_numeric($segment)) {
            $this->urlSegment = $segment;
        }
        return $this;
    }
    
    /**
     * @return Request
     */
    public function enableRestfulMapping()
    {
        $this->isRestfulMappingEnabled = true;
        return $this;
    }
    
    /**
     * @return Request
     */
    public function enableUrlRewriting()
    {
        $this->isUrlRewritingEnabled = true;
        return $this;
    }
    
    /**
     * @param array $routes
     * @return Request
     */
    public function addRoutes(array $routes)
    {
        if (! ($this->router instanceof Router)) {
            $this->router = new Router($this->getParams());
        }
        $this->router->addRoutes($routes);
        return $this;
    }
    
    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }
    
    /**
     * @param string $name
     */
    public function setController($name)
    {
        if (null !== $name && '' !== $name) {
            $strUtil = Loader::getInstance()->get('StringUtil');
            $this->controller = $strUtil->camelize($name);
        }
    }
    
    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;   
    }
    
    /**
     * @param string $name
     */
    public function setAction($name)
    {
        if (null !== $name && '' !== $name) {
            $strUtil = Loader::getInstance()->get('StringUtil');
            $name = $strUtil->camelize($name);
            $name{0} = strtolower($name{0});
            $this->action = $name;
        }
    }
    
    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
    
    /**
     * @return void
     * @throws RequestException
     * @throws RuntimeException
     */
    public function dispatch()
    {
        if ($this->isUrlRewritingEnabled && $this->router instanceof Router) {
            $this->getRouter()->route($this);
        } else if ($this->isUrlRewritingEnabled) {
            $this->parseUrl();
        } else {
            $this->parseQueryString(); 
        }
        
        $controllerName = $this->getController();
        $actionName = $this->getAction();
        try {
            // instantiate controller
            $controller = Loader::getInstance()->getController($controllerName);
        } catch(LoaderException $e) {
             throw new RequestException($e->getMessage(), Response::NOT_FOUND);
        }
        
        $controllerResponse = $controller->init($this);
        if (! ($controllerResponse instanceof Response)) {
            if (! method_exists($controller, $actionName . 'Action')) {
                $m = sprintf('Method "%s" not found', $actionName);
                throw new RequestException($m, Response::NOT_FOUND);
            }
            // call action method
            $controllerResponse = $controller->{$actionName . 'Action'}($this);
        }
        
        if ($controllerResponse instanceof Exception) {
            $format = $this->getParam('format', 'html');
            $response = new Response();
            $response->setAcceptableTypes(array($format));
            $response->setResponseType($format);
            $response->setException($controllerResponse);
        } else if ($controllerResponse instanceof View) {
            $response = new Response();
            $response->setView($controllerResponse);
        } else if ($controllerResponse instanceof Response) {
            $response = $controllerResponse;
            $response->setResponseType($this->getParam('format'));
        } else {
            $m = sprintf('Method "%s::%s" contains an invalid return type', $controllerName, $actionName);
            throw new RuntimeException($m);
        }
        
        $view = new Renderer();
        $view->render($this, $response);
    }
    
    /**
     * @return void
     */
    public function parseUrl()
    {        
        $parts = $this->getUrlParts();
        $partsCount = count($parts);
        
        $controller = isset($parts[0]) ? $parts[0] : $this->getController();
        $action = null;
        $method = $this->getMethod();
        $methods = array(
            self::METHOD_GET => self::ACTION_SHOW, 
            self::METHOD_PUT => self::ACTION_UPDATE,
            self::METHOD_DELETE => self::ACTION_DESTROY
        );

        $params = array();
        $offset = 1;

        if (!isset($parts[1]) && self::METHOD_GET === $method) {
            $action = $this->getAction();
        } else if (!isset($parts[1]) && self::METHOD_POST === $method) {
            $action = self::ACTION_CREATE;
        } else if (isset($parts[1]) && isset($methods[$method])) {
            $offset = 2;
            $this->setParam('id', $parts[1]);
            $action = $methods[$method];
        } else {
            throw new RequestException('Method not allowed', Response::NOT_ALLOWED);
        }
        
        $params = array_slice($parts, $offset);
        for ($i = 0; $i < count($params); $i++) {
             $this->setParam('key'.$i, $params[$i]);
        }
        
        if (null === $action) {
            $action = isset($parts[1]) ? $parts[1] : $this->getAction();
        }
        
        $this->setController($controller);
        $this->setAction($action);
    }
    
    /**
     * @return void
     * @throws RequestException
     */
    public function parseQueryString()
    {
        $path = preg_replace('/[^a-z.]/i', '', rtrim($this->getParam('method', ''), '.'));
        $parts = explode('.', $path);
        $controller = isset($parts[0]) ? $parts[0] : $this->getController();
        $action = isset($parts[1]) ? $parts[1] : $this->getAction();
        
        if ($this->isRestfulMappingEnabled) {
            $method = $this->getMethod();
            $methods = array(
                self::METHOD_GET => self::ACTION_SHOW, 
                self::METHOD_PUT => self::ACTION_UPDATE,
                self::METHOD_DELETE => self::ACTION_DESTROY
            );
        
            if (!$this->hasParam('id') && self::METHOD_GET === $method) {
                $action = $this->getAction();
            } else if (!$this->hasParam('id') && self::METHOD_POST === $method) {
                $action = self::ACTION_CREATE;
            } else if ($this->hasParam('id') && isset($methods[$method])) {
                $action = $methods[$method];
            } else {
                throw new RequestException('Method not allowed', Response::NOT_ALLOWED);
            }
        }
        
        $urlPath  = '?method=' . $path;
        $urlPath .= $this->hasParam('id') ? '&id=' . $this->getParam('id') : '';
        $this->setUrlPath($urlPath);
        
        $this->setController($controller);
        $this->setAction($action);
    }
    
    /**
     * @param array $parts
     */
    public function setUrlParts(array $parts)
    {
        $this->urlParts = $parts;
    }
    
    /**
     * Parses the URL path and returns an array of strings containing the 
     * segments found after the keyword. For example: 
     * 
     * http://www.mysite.com/foo/api/users/1/show -> array('users', '1', 'show')
     * 
     * @return array
     * @throws RuntimeException
     * @throws RequestException
     */
    public function getUrlParts()
    {
        if (null !== $this->urlParts) {
            return $this->urlParts;
        }
        
        $parts = array();
        $baseUrl = $this->getBaseUrl();
        $urlPath = $this->getUrlPath();
        $urlPathArray = explode('/', ltrim($urlPath, '\/'));
        
        if (null === $this->urlKeyword) {
            $parts = $urlPathArray;
        } else if (null !== $this->urlSegment) {
            // position defined by the user
            if (0 === $this->urlSegment) {
                $parts = $urlPathArray;
            } else if (isset($urlPathArray[$this->urlSegment-1]) 
                && $this->urlKeyword === $urlPathArray[$this->urlSegment-1]) {
                $parts = array_slice($urlPathArray, $this->urlSegment);
            } else {
                throw new RuntimeException(sprintf('Keyword "%s" not found in URL', $this->urlKeyword));
            }            
        } else {
            // find keyword
            $keywordHost = $this->urlKeyword . '.';
            $keywordPath = '/' . $this->urlKeyword;
            if ($keywordHost === substr($baseUrl, 0, strlen($keywordHost))) {
                // keyword found in host name
                $parts = $urlPathArray;
            } else if (false !== strpos($urlPath, $keywordPath)) {
                // keyword found in url path
                $found = false;
                for ($i = 0; $i < count($urlPathArray); $i++) {
                    if ($found) {
                        $parts[] = $urlPathArray[$i];
                    } else if ($this->urlKeyword == $urlPathArray[$i]) {
                        $found = true;
                    }
                }
            } else {
                throw new RuntimeException(sprintf('Keyword "%s" not found in URL', $this->urlKeyword));
            }
        }
        
        // always return an array with at least one element
        if (! isset($parts[0])) {
            $parts = array('index'); 
        }
        $this->setUrlParts($parts);
        
        return $this->urlParts;   
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return sprintf('%s://%s', isset($_SERVER['HTTPS']) ? 'https' : 'http', $_SERVER['SERVER_NAME']);
    }
    
    /**
     * @return string
     */
    public function setUrlPath($path)
    {
        $this->urlPath = $path;
    }
    
    /**
     * @return string
     * @throws RuntimeException
     */
    public function getUrlPath()
    {
        if (null !== $this->urlPath) {
            return $this->urlPath;
        }
        
        $urlPath = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $urlPath = $_SERVER['REQUEST_URI'];
            $strPos = strpos($urlPath, '?');
            if (false !== $strPos) {
                $urlPath = substr($urlPath, 0, $strPos);
            }
        } else {
            $filename = (isset($_SERVER['SCRIPT_FILENAME'])) ? basename($_SERVER['SCRIPT_FILENAME']) : '';
            if (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === $filename) {
                $urlPath = $_SERVER['SCRIPT_NAME'];
            } else 
                if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) === $filename) {
                    $urlPath = $_SERVER['PHP_SELF'];
                }
        }
        
        if (empty($urlPath)) {
            throw new RuntimeException('URL path is undefined');
        }
                
        // format
        $formatPos = strrpos(basename($urlPath), '.');
        if (false !== $formatPos) {
            $format = substr(basename($urlPath), $formatPos+1);
            $this->setParam('format', $format);
            $urlPath = substr($urlPath, 0, strrpos($urlPath, '.')); 
        }
        
        $urlPath = preg_replace('/\/+/', '\1/', rtrim($urlPath, '\/'));
        if (empty($urlPath)) {
            $urlPath = '/';
        }
        $this->setUrlPath(urldecode($urlPath));
        
        return $this->urlPath;
    }
    
    /**
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        if (! array_key_exists($key, $this->params)) {
            return $default;
        }
        return $this->params[$key];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Request
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function hasParam($key)
    {
        return array_key_exists($key, $this->params);
    }

    /**
     * @param array $params
     * @return Request
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * @param array $params
     * @return Request
     */
    public function setPost(array $params)
    {
        $_POST = $params;
        return $this;
    }
    
    /**
     * @param string $key
     * @return boolean
     */
    public function hasPost($key)
    {
        return array_key_exists($key, $_POST);
    }

    /**
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getPost($key = null, $default = null)
    {
        if (null === $key) {
            return $_POST;
        }
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * @param string $uri
     * @param integer $code HTTP status codes
     * @return void
     */
    public function redirect($uri, $code = Response::FOUND)
    {
        $response = new Response('html');
        $response->setCode($code);
        $response->addHeader('Location: ' . $uri);
        $response->sendHeaders();
    }
    
    /**
     * Get a singleton instance of Session.
     * 
     * @return Session
     */
    public function getSession()
    {
        return Loader::getInstance()->get('Session');
    }
   
    /**
     * @param Exception $e
     * @return void
     * @throws Exception
     */
    public function handleException(Exception $e)
    {
        if ($e instanceof RuntimeException) {
            throw $e;
        }
        
        $response = new Response(array('html', 'json', 'xml'));
        $response->setException($e);
        
        try {
            $view = new Renderer();
            $view->render($this, $response);
        } catch (Exception $e) {
            throw $e;
        }
    }
}