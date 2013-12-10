#Controller

## Action Controllers

The Controller provides a glue between the domain model objects and transport layer events. Like the HTTP interface, Controllers are Request/Response oriented.

Controllers handle incoming HTTP requests, interact with the model to get data, and direct domain data to the Response object for display.

The full request object is injected via the action method and is primarily used to query for request parameters, whether they come from a GET or POST request, or from the URL. The request parameters are made available to the action through the accessor methods getParam(key, default_value) and getParams().

### HTTP Request

    GET /api/users/1/?api_key=value

### Controller

    class UsersController extends Controller
    {
        /**
         * GET /users/1.json
         * GET /users/1.xml
         */
        public function showAction($request)
        {
        	// only accept JSON and XML
            $request->acceptContentTypes(array('json', 'xml'));

            $response = new Response();        
            $response->id = $request->getParam('id');
            $response->api_key = $request->getParam('api_key');
            
            return $response;
        }
    }

You can customize instantiation using the init() method, which is called by the request object before calling the action method:

    class UsersController extends Controller
    {
        public function init($request)
        {
            if (! $request->hasParam('api_key')) {
                // throwing or returning an Exception terminates the dispatch loop
                throw new Exception('Missing parameter: api_key', Response::FORBIDDEN);
            }
            
            if ('show' === $request->getMethod()) {
                // only accept JSON and XML
                $request->acceptContentTypes(array('json', 'xml'));
            }
        }

        public function showAction($request)
        {
            $response = new Response();        
            $response->id = $request->getParam('id');
            $response->api_key = $request->getParam('api_key');
            
            return $response;
        }
    }

## Error Messages

When Apify returns error messages, it does so in your requested format.

An error from a JSON request might look like this:

    class UsersController extends Controller
    {
        public function indexAction($request)
        {
            $request->acceptContentTypes(array('json', 'xml'));

            $response = new Response();
            if (! $request->hasParam('api_key')) {
                throw new Exception('Missing parameter: api_key', Response::FORBIDDEN);
            }
            $response->api_key = $request->getParam('api_key');

            return $response;
        }
    }

HTTP Request

    GET /users.json

Response

    Status: 403 Forbidden
    Content-Type: application/json
    {
        "code": 403,
        "error": {
            "message": "Missing parameter: api_key",
            "type": "Exception"
        }
    }

## Error Templates

Error templates are rendered when an unhandled exception occurs during the request processing.

When an error occurs and an exception is thrown, Apify displays an error page. The page content depends on the environment. The default error templates are stored in the `app/views/error` directory:

    app/views/error/development.phtml
    app/views/error/production.phtml

When you customize an error message template, you have access to the following variables:

The Request Object

    $request->getMethod()
    $request->getController()
    $request->getAction()
    $request->hasParam('key')
    $request->getParam('key', 'default_value')
    $request->getSession()

The Response Object

    $response->getCode()
    $response->getError()
    $response->getHeaders()
    $response->getException()
    $response->getData()
    $response->toArray()
