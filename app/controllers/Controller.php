<?php
/**
 * Controller is an abstract class you may use for implementing ZF-style Action 
 * Controllers.
 * 
 * The most basic operation is to subclass Controller, and create action methods 
 * that correspond to the various actions you wish the controller to handle for 
 * your site. Apify's routing and dispatch handling will autodiscover any methods 
 * ending in 'Action' in your class as potential controller actions. 
 *
 * Subclassing Controller is optional. 
 */
abstract class Controller
{
    /**
     * @var null|View
     */
    protected $view;
    
    /**
     * @var null|Request
     */
    protected $request;
        
    /**
     * Zend_Controller_Action::init() method.
     * 
     * Initialize object. Method called by the Request object.
     *
     * @param Request $request
     * @return void
     */
    public function init($request) 
    {}

    /**
     * Zend_Controller_Action::setRequest() method.
     * 
     * Sets the Request object
     *
     * @param Request $request
     * @return self
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }
    
    /**
     * Zend_Controller_Action::getRequest() method.
     * 
     * Returns the Request object
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
     * Sets a parameter in the Request object.
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
     * Determines whether a given parameter exists in the Request object.
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
     * Returns all parameters in the Request object as an associative array.
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
     * Redirects to another URL.
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
