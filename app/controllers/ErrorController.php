<?php
class ErrorController
{
    /**
     * This action will be called by the Request object when an exception has
     * been encountered. 
     * 
     * @return Response
     */
    public function errorAction($request) 
    {
        $response = $request->getResponse();
        $viewScript = DEBUG ? 'development' : 'production';
        
        // get the Exception from the Response
        $exception = $response->getException();
        switch ($exception->getCode()) { 
            case Response::NOT_FOUND:
                // 404 error - controller or action not found
                $viewScript = 'pagenotfound';
                break;
            case Response::NOT_ACCEPTABLE:
                // 406 error - content type missing or invalid
                break;
            default: 
                // other error code
                break; 
        }
        
        if ('html' === $request->getContentType()) {
            $view = new View();
            $view->setScript($viewScript);
            $view->setLayout('error');
            $response->setView($view);
        }
        
        return $response;
    } 
}
