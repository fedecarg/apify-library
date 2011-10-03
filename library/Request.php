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
    protected $contentType = 'html';
    
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
     * @var Response
     */
    protected $response;
    
    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->setParams(array_merge($_GET, $_POST));
    }
    
    /**
     * @params string $keyword
     * @return Request
     */
    public function setUrlKeyword($keyword)
    {
        $this->urlKeyword = $keyword;
        return $this;
    }
    
    /**
     * @params int $segment
     * @return Request
     */
    public function setUrlSegment($segment)
    {
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
     * @param null|string $controllerName
     * @param null|string $actionName 
     * @return void
     * @throws RuntimeException
     */
    public function dispatch($controllerName = null, $actionName = null)
    {
        if ($this->isUrlRewritingEnabled && $this->router instanceof Router) {
            $this->getRouter()->route($this);
        } else if ($this->isUrlRewritingEnabled) {
            $this->parseUrl();
        } else {
            $this->parseQueryString(); 
        }
        
        $controllerName = isset($controllerName) ? $controllerName : $this->getController(); 
        $actionName = isset($actionName) ? $actionName : $this->getAction();
        
        $response = $this->handleRequest($controllerName, $actionName);
        $this->setResponse($response);
        
        $renderer = new Renderer();
        $renderer->render($this);
    }
    
    
    /**
     * When a Controller has been found, the handleRequest method will be invoked, 
     * which is responsible for handling the actual request and - if applicable - returning 
     * an appropriate Response. So actually, this method is the main entrypoint for the 
     * dispatch loop which delegates requests to controllers. 
     * 
     * @param string $controllerName
     * @param string $actionName 
     * @return mixed
     * @throws RequestException
     * @throws RuntimeException
     */
    public function handleRequest($controllerName, $actionName)
    {    
        try {
            $controller = Loader::getInstance()->getController($controllerName);
        } catch (LoaderException $e) {
            throw new RequestException($e->getMessage(), Response::NOT_FOUND);
        }
        
        if (method_exists($controller, 'setRequest')) {
            $controller->setRequest($this);
        }
        
        $controllerResponse = null;
        if (method_exists($controller, 'init')) {
            $controllerResponse = $controller->init($this);
        }
        
        if (! isset($controllerResponse)) {
            if (! method_exists($controller, $actionName . 'Action')) {
                $m = sprintf('Method "%s" not found', $actionName);
                throw new RequestException($m, Response::NOT_FOUND);
            }
            $controllerResponse = $controller->{$actionName . 'Action'}($this);
        }
        
        $this->setController($controllerName);
        $this->setAction($actionName);
        
        if ($controllerResponse instanceof View) {
            $this->setContentType('html');
            $response = new Response();
            $response->setView($controllerResponse);
            return $response;      
        } else if ($controllerResponse instanceof Response) {
            return $controllerResponse;
        } else {        
            $m = sprintf('Method "%s::%sAction()" contains an invalid return type', $controllerName, $actionName);
            throw new RuntimeException($m);
        }
    }
    
    /**
     * @param Exception $e
     * @return void
     * @throws Exception
     */
    public function handleException(Exception $e)
    {
        // don't render runtime exceptions
        if ($e instanceof RuntimeException) {
            throw $e;
        }
        
        $response = new Response();
        $response->setException($e);
        $this->setResponse($response);
        
        try {
            $this->dispatch('Error', 'error');
        } catch (Exception $e) {
            throw $e;
        }
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
        if ($this->hasParam('format')) {
            $this->setContentType($this->getParam('format'));
        }
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
        
        $this->setUrlParts($parts);
        
        return $parts;   
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
            } else {
                if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) === $filename) {
                    $urlPath = $_SERVER['PHP_SELF'];
                }
            }
        }
        
        if (empty($urlPath)) {
            throw new RuntimeException('URL path is undefined');
        }
        
        // content negotiation
        $extensionPos = strrpos(basename($urlPath), '.');
        if (false !== $extensionPos) {
            $contentType = substr(basename($urlPath), $extensionPos+1);
            $this->setContentType($contentType);
            $urlPath = substr($urlPath, 0, strrpos($urlPath, '.')); 
        } else if ($this->hasParam('format')){
            $contentType = $this->getParam('format');
        } else if ($this->isRestfulMappingEnabled) {
            $contentType = $this->getAcceptHeader();
            if (null !== $contentType) {
                $this->setContentType($contentType);
            }
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
     * @param string $type
     * @return Request
     */
    public function setContentType($type)
    {
        $this->contentType = $type;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }
    
    /**
     * @return boolean
     */
    public function hasContentType()
    {
        return null !== $this->contentType;
    }
    
    /**
     * Restricts the Content Types allowed.
     * 
     * @param array $types
     * @return void
     * @throws RequestException
     */
    public function acceptContentTypes(array $types)
    {
        if (! in_array($this->getContentType(), $types)) {
            $this->setContentType('html');
            throw new RequestException('Not Acceptable', Response::NOT_ACCEPTABLE);   
        }
    }
    
    /**
     * @return null|string
     */
    public function getAcceptHeader()
    {
        $accept = explode(',', $_SERVER['HTTP_ACCEPT']);
        $type = null;
        if (isset($accept[0])) {
            $response = new Response();
            $mimeTypes = $response->getMimeTypes();
            $type = array_search($accept[0], $mimeTypes);
            if (false === $type) {
                $type = null;
            }
        }
        return $type;
    }
    
    /**
     * @return Session
     */
    public function getSession()
    {
        return Loader::getInstance()->get('Session');
    }
    
    /**
     * Return the Response object
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the Response object
     *
     * @param Response $response
     * @return self
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }
    
    /**
     * @param string $uri
     * @param integer $code HTTP status codes
     * @return void
     */
    public function redirect($uri, $code = Response::FOUND)
    {
        $response = new Response();
        $response->setCode($code);
        $response->addHeader('Location: ' . $uri);
        $response->sendHeaders();
        exit;
    }
}