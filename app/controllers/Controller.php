<?php
abstract class Controller
{
    /**
     * @var null|View
     */
    protected $view;
        
    /**
     * Method called by the request object.
     *
     * @param Request $request
     * @return void
     */
    public function init($request) 
    {}
    
    /**
     * Proxy method for Loader::getModel()
     */
    public function getModel($name) 
    {
        return Loader::getInstance()->getModel($name);
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
     * Returns an new instance of the View object.
     * 
     * @param sring $layout
     * @return View 
     */
    public function initView($layout = 'main')
    {
        $view = new View();
        $view->setLayout($layout);
        
        return $view;
    }
}
