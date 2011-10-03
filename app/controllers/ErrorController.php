<?php
class ErrorController extends Controller
{
    /**
     * This action will be called by the Request object.  
     * 
     * @return Response|View
     */
    public function errorAction($request) 
    {
        if ('html' === $request->getContentType()) {
            $script = DEBUG ? 'development' : 'production';
            $response = new View($script);
            $response->setLayout('error');
        } else {
            $response = new Response();
        }
        
        $exception = $request->getResponse()->getException();
        switch ($exception->getCode()) { 
            case Response::NOT_FOUND:
                // 404 error - controller or action not found
                break;
            case Response::NOT_ACCEPTABLE:
                // 406 error - content type missing or invalid
                break;
            default: 
                // other error code
                break; 
        }
        
        return $response;
    } 
}
