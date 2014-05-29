<?php
class IndexController extends Apify_Controller
{
    /**
     * The init() method is called by the request object after the controller
     * is instantiated.
     * 
     * @param Apify_Request $request
     * @return void
     * @throws Exception
     */
    public function init($request)
    {
        if (! method_exists($this, $request->getAction().'Action')) {
            $message = sprintf('%s(): Intercepted call to "%s" action', __METHOD__, $request->getAction());
            throw new Exception($message, Apify_Response::NOT_FOUND);
        }
    }
    
    /** 
     * @route GET /
     * 
     * @param Apify_Request $request
     * @return View
     */
    public function indexAction($request)
    {
        $view = new Apify_View();
        $view->setLayout('main');
        
        return $view;
    }
    
    /**
     * Returns a View object.
     * 
     * @route GET /?method=example.request
     * @route GET /example/request
     * 
     * @param Apify_Request $request
     * @return View
     */
    public function requestAction($request) 
    {
        $view = new Apify_View();
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
     * Returns a Response object.
     * 
     * @route GET /?method=example.response&format=json
     * @route GET /?method=example.response&format=xml
     * @route GET /example/response.json
     * @route GET /example/response.xml
     * 
     * @param Apify_Request $request
     * @return Apify_Response
     */
    public function responseAction($request) 
    {
        // accept JSON and XML
        $request->acceptContentTypes(array('json', 'xml'));
        
        $response = new Apify_Response();
        $response->statusCode = $response->getCode();
        $response->contentType = $request->getContentType();
        $response->key = 'value';
        
        return $response;
    }
    
    /**
     * Returns either a View or Response object.
     * 
     * @route GET /?method=example.mixed
     * @route GET /example/mixed
     * 
     * @param Apify_Request $request
     * @return Apify_View|Apify_Response
     */
    public function mixedAction($request) 
    {
        // accept HTML, JSON and XML
        $request->acceptContentTypes(array('html', 'json', 'xml'));
        
        if ('html' === $request->getContentType()) {
            $response = new Apify_View();
            $response->setLayout('main');
        } else {
            $response = new Apify_Response();
        }
        
        $response->method = $request->getMethod();
        $response->controller = $request->getController();
        $response->action = $request->getAction();
            
        return $response;
    }
    
}
