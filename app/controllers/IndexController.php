<?php
class IndexController extends Controller
{
    /**
     * The init() method is called by the request object after the controller
     * is instantiated.
     * 
     * @param Request $request
     * @return void|Exception
     * @throws Exception
     */
    public function init($request)
    {
        if (! method_exists($this, $request->getAction().'Action')) {
            $request->setDefaultContentType('html');
            $message = sprintf('%s(): Intercepted call to "%s" action', __METHOD__, $request->getAction());
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
     * Returns a Response object.
     * 
     * @route GET /?method=example.response&format=json
     * @route GET /?method=example.response&format=xml
     * @route GET /example/response.json
     * @route GET /example/response.xml
     * 
     * @param Request $request
     * @return Response
     */
    public function responseAction($request) 
    {
        // $request->setDefaultContentType('json');
        $request->acceptContentTypes(array('json', 'xml'));
        
        $response = new Response();
        $response->statusCode = $response->getCode();
        $response->responseType = $request->getParam('format');
        $response->allowedTypes = $request->getAcceptableTypes();
        $response->key = 'value';
        
        return $response;
    }
    
    /**
     * Returns either a View or Response object.
     * 
     * @route GET /?method=example.mixed
     * @route GET /example/mixed
     * 
     * @param Request $request
     * @return View|Response
     */
    public function mixedAction($request) 
    {
        $request->setDefaultContentType('html');
        $request->acceptContentTypes(array('html', 'json', 'xml'));
        
        if ('html' === $request->getParam('format')) {
            $response = new View();
            $response->setLayout('main');
        } else {
            $response = new Response();
        }
        
        $response->method = $request->getMethod();
        $response->controller = $request->getController();
        $response->action = $request->getAction();
            
        return $response;
    }
    
}
