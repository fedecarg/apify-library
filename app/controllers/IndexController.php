<?php
class IndexController extends Controller
{
    /**
     * The init() method is called by the request object after the controller
     * is instantiated.
     * 
     * @param Request $request
     * @return void
     */
    public function init($request)
    {
        if (! method_exists($this, $request->getAction())) {
            $message = sprintf('Intercepted call to "%s" action', $request->getAction());
            throw new Exception($message, Response::NOT_FOUND);
        }
    }
    
    /** 
     * @route GET /
     * 
     * @param Request $request
     * @return View
     */
    public function indexAction($request)
    {
        return $this->initView();
    }
    
    /**
     * @route GET /?method=apify.request
     * @route GET /apify/request
     * 
     * @param Request $request
     * @return View
     */
    public function requestAction($request) 
    {
        $view = $this->initView();
        
        $view->method = $request->getMethod();
        $view->controller = $request->getController();
        $view->action = $request->getAction();
        $view->baseUrl = $request->getBaseUrl();
        $view->urlPath = $request->getUrlPath();
        $view->params = $request->getParams();
        
        return $view;
    }
    
    /**
     * @route GET /?method=apify.response
     * @route GET /apify/response
     * 
     * @param Request $request
     * @return View
     */
    public function responseAction($request) 
    {
        $view = $this->initView();
        
        $view->statusCode = $response->getCode();
        $view->responseType = $response->getResponseType();
        $view->allowedTypes = $response->getAllowedTypes();
        $view->key = 'value';
        
        return $view;
    }
}
