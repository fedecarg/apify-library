# Request

## URL Dispatcher

Apify lets you design URLs however you want, with no limitations.

The Apify request object parses the URL to determine the value of the controller and action keys before dispatching the incoming request. It supports Web Services and RESTful Web Services.

### Web Services

    /?method=users
    /?method=users.show&id=1

Clean URL scheme:

    /users
    /users/1
    /users/1/update

To implement a clean URL scheme you need to enable URL rewriting and add at least one route:

    $routes[] = new Route('/users/:id', 
        array(
            'controller' => 'users',
            'action'     => 'show'
        )
    );

    $routes[] = new Route('/users/:id/:action', 
        array(
            'controller' => 'users'
        ),
        array(
            'action'     => '(update|destroy)',
            'page'       => '\d+'
        )
    );

    $request = new Request();
    $request->enableUrlRewriting();
    $request->addRoutes($routes);
    $request->dispatch();

### RESTful Web Services

    GET /?method=users
    GET /?method=users&id=1
    POS /?method=users
    PUT /?method=users&id=1
    DEL /?method=users&id=1

Clean URL scheme:

    GET /users
    GET /users/1
    POS /users
    PUT /users/1
    DEL /users/1

To enable this functionality:

    $request = new Request();
    $request->enableUrlRewriting();
    $request->enableRestfulMapping();
    $request->dispatch();

### Relative URLs
By default, the request object uses the URL path to determine the route. If you are using relative URLs, you can configure the request object to rewrite the original URL path. For example:

URL path: `/api/users/1`

    // rewrite to /users/1
    $request->setUrlSegment(1);

URL path: `/api/v1/users/1`

    // rewrite to /users/1
    $request->setUrlSegment(2);

This allows you to manage several different applications using a common installation:

    <VirtualHost *>
        ServerName www.mysite.com

        # ...
        
        # App 1
        <Location "/api/v1">
            SetEnv APP_NAME app1
            SetEnv URL_SEGMENT 2
        </Location>

        # App 2
        <Location "/api/v2">
            SetEnv APP_NAME app2
            SetEnv URL_SEGMENT 2
        </Location>
        
    </VirtualHost>

And, in your index.php file:

    define('APP_DIR',  dirname(__FILE__) . '/' . $_SERVER['APP_NAME']);
        
    try {
        $request = new Request();
        $request->setUrlSegment($_SERVER['URL_SEGMENT']);
        $request->enableUrlRewriting();
        $request->dispatch();
    } catch (Exception $e) {
        $request->handleException($e);
    }


## Content Negotiation

Apify implements content negotiation by parsing the URI and the Accept header.

Content negotiation is an HTTP feature that allows an HTTP server to serve different media types for the same URL, according to which media types are requested by the HTTP client. Content negotiation is more likely to be used by custom clients, such as an Ajax request that requires a JSON response.

Apify implements content negotiation by parsing the URI and the Accept header. The problem with libraries that only parse the Accept header is that some client-side platforms have limitations in their support of HTTP (specially web browsers). Apify overcomes those limitations by parsing the query string, extension and Accept header.

### Query String
If URL Rewriting is disabled, specify your response format in the query string. This means a format=xml or format=json parameter for XML or JSON, respectively, which will override the Accept header if there is one.

    /?method=users&format=json
    /?method=users&format=xml

### Extension
If URL Rewriting is enabled, append a format extension to the end of the URL path (.html, .json, .xml or .rss).

    /users.json
    /users.xml

### Accept Header
If RESTful Mapping is enabled, send a standard Accept header in your request (text/html, application/xml or application/json).

    Accept: application/xml

**Note**: Extension and query string overwrites the Accept header.

### Acceptable Formats
Action methods can use the $request->acceptContentTypes() method to specify certain media types which are acceptable for the response.

    class UsersController extends Controller
    {
        /** 
         * Route /users.json
         * Route /users.xml
         *
         * @param Request $request
         * @return Response
         */
        public function indexAction($request)
        {
            $request->acceptContentTypes(array('json', 'xml'));

            $response = new Response();
            $response->users = array('paul', 'adrian', 'adam');

            return $response;
        }
    }

You can also make JSON the default format:

    class UsersController extends Controller
    {
        /** 
         * Route /users
         * Route /users.json
         *
         * @param Request $request
         * @return Response
         */
        public function indexAction($request)
        {
            $request->setContentType('json');

            $response = new Response();
            $response->users = array('paul', 'adrian', 'adam');

            return $response;
        }
    }
