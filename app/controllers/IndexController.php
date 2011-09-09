<?php
class IndexController extends Controller
{
    /**
     * The init() method is called by the request object after the controller
     * is instantiated.
     * 
     * @param Request $request
     * @return void|Exception
     */
    public function init($request)
    {
        if (! method_exists($this, $request->getAction().'Action')) {
            $message = sprintf('%s(): Intercepted call to "%s" action', __METHOD__, $request->getAction());
            return new Exception($message, Response::NOT_FOUND);
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
     * Returns a View object.
     * 
     * @route GET /?method=example.request
     * @route GET /example/request
     * 
     * @param Request $request
     * @return View
     */
    public function requestAction($request) 
    {
        $view = new View();
        $view->setLayout('main');
        
        $view->method = $request->getMethod();
        $view->controller = $request->getController();
        $view->action = $request->getAction();
        $view->baseUrl = $request->getBaseUrl();
        $view->urlPath = $request->getUrlPath();
        $view->params = $request->getParams();
        
        return $view;
    }
    
    /**
     * Returns a Response object (JSON or XML).
     * 
     * @route GET /?method=example.response
     * @route GET /example/response
     * 
     * @param Request $request
     * @return Response
     */
    public function responseAction($request) 
    {
        $response = new Response(array('json', 'xml'));
        if (! $request->hasParam('format')) {
            // set default format
            $request->setParam('format', 'json');
        }
        
        $response->statusCode = $response->getCode();
        $response->responseType = $response->getResponseType();
        $response->allowedTypes = $response->getAcceptableTypes();
        $response->key = 'value';
        
        return $response;
    }
    
    /**
     * Returns either a View object or a Response object.
     * 
     * @route GET /?method=example.mixed
     * @route GET /example/mixed
     * 
     * @param Request $request
     * @return View|Response
     */
    public function mixedAction($request) 
    {
        $response = new Response(array('json', 'xml'));
        if (! $request->hasParam('format')) {
            // return html
            $response = new View();
            $response->setLayout('main');
        }
        
        $response->method = $request->getMethod();
        $response->controller = $request->getController();
        $response->action = $request->getAction();
            
        return $response;
    }
    
}
