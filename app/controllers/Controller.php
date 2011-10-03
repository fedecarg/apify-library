<?php
abstract class Controller
{
    /**
     * @var null|View
     */
    protected $view;
    
    /**
     * @var Request
     */
    protected $request;
        
    /**
     * Zend_Controller_Action::init() method.
     * 
     * Initialize object (method called by the Request object).
     *
     * @param $request
     * @return void
     */
    public function init($request) 
    {}

    /**
     * Zend_Controller_Action::setRequest() method.
     * 
     * Set the Request object
     *
     * @param Request $request
     * @return self
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
    
    /**
     * Zend_Controller_Action::getRequest() method.
     * 
     * Return the Request object
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Zend_Controller_Action::_getParam() method.
     * 
     * Gets a parameter from the Request object. If the parameter does not exist, 
     * NULL will be returned. If $default is set, then $default will be returned 
     * instead of NULL.
     *
     * @param string $paramName
     * @param mixed $default
     * @return mixed
     */
    protected function _getParam($paramName, $default = null)
    {
        $value = $this->getRequest()->getParam($paramName);
         if ((null === $value || '' === $value) && (null !== $default)) {
            $value = $default;
        }

        return $value;
    }
    
    /**
     * Zend_Controller_Action::_setParam() method.
     * 
     * Set a parameter in the Request object.
     *
     * @param string $paramName
     * @param mixed $value
     * @return self
     */
    protected function _setParam($paramName, $value)
    {
        $this->getRequest()->setParam($paramName, $value);
        return $this;
    }
    
    /**
     * Zend_Controller_Action::_hasParam() method.
     * 
     * Determine whether a given parameter exists in the Request object.
     *
     * @param string $paramName
     * @return boolean
     */
    protected function _hasParam($paramName)
    {
        return null !== $this->getRequest()->hasParam($paramName);
    }

    /**
     * Zend_Controller_Action::_getAllParams() method.
     * 
     * Return all parameters in the Request object as an associative array.
     *
     * @return array
     */
    protected function _getAllParams()
    {
        return $this->getRequest()->getParams();
    }
    
    /**
     * Zend_Controller_Action::_redirect() method.
     * 
     * Redirect to another URL.
     *
     * @param string $url
     * @param array $options Options to be used when redirecting
     * @return void
     */
    protected function _redirect($uri, $code = Response::FOUND)
    {
        $this->getRequest()->redirect($uri, $code);
    }
    
    /**
     * Zend_Controller_Action::initView() method.
     * 
     * Returns an new instance of the View object.
     * 
     * @return View 
     */
    public function initView()
    {
        $this->view = new View();
        return $this->view;
    }
    
    /**
     * @param View $view
     */
    public function setView(View $view)
    {
        $this->view = $view;
    }
    
    /**
     * Returns an single instance of the View object.
     * 
     * @return null|View 
     */
    public function getView()
    {
        return $this->view;
    }
    
    /**
     * Zend_Controller_Action::render() method.
     * 
     * @return null|View 
     * @throws RuntimeException
     */
    public function render($script = null)
    {
        if (! isset($this->view)) {
            throw RuntimeException(__METHOD__ . ' no View object set; unable to render view');
        }
        $this->view->setScript($script);
        
        return $this->view;
    }
    
    /**
     * Proxy method for Loader::getModel()
     */
    public function getModel($name) 
    {
        return Loader::getInstance()->getModel($name);
    }
}
