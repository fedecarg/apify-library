# Response

## Building a Web Service (API)

Apify allows you to add a web API to any existing website.

In Apify, Controllers handle incoming HTTP requests, interact with the model to get data, and direct domain data to the response object for display. The full request object is injected via the action method and is primarily used to query for request parameters, whether they come from a GET or POST request, or from the URL.

Creating a RESTful web API with Apify is easy. Each action results in a response, which holds the headers and document to be sent to the user’s browser. You are responsible for generating the response object inside the action method.

    class UsersController extends Controller
    {
        public function indexAction($request)
        {
            // 200 OK
            return new Response();
        }
    }

The response object describes the status code and any headers that are sent. The default response is always 200 OK, however, it is possible to overwrite the default status code and add additional headers:

    class UsersController extends Controller
    {
        public function indexAction($request)
        {
            $response = new Response();

            // 401 Unauthorized
            $response->setCode(Response::UNAUTHORIZED);

            // Cache-Control header
            $response->setCacheHeader(3600);

            // ETag header
            $response->setEtagHeader(md5($request->getUrlPath()));

            // X-RateLimit header
            $limit = 300;
            $remaining = 280;
            $response->setRateLimitHeader($limit, $remaining);

            // Raw header
            $response->addHeader('Edge-control: no-store');

            return $response;
        }
    }

### Content Negotiation

Apify supports sending responses in HTML, XML, RSS and JSON. In addition, it supports JSONP, which is JSON wrapped in a custom JavaScript function call. There are 3 ways to specify the format you want:

Appending a format extension to the end of the URL path (.html, .json, .rss or .xml)
Specifying the response format in the query string. This means a format=xml or format=json parameter for XML or JSON, respectively, which will override the Accept header if there is one.
Sending a standard Accept header in your request (text/html, application/xml or application/json).
The acceptContentTypes() method indicates that the request only accepts certain content types:

    class UsersController extends Controller
    {
        public function indexAction($request)
        {
        	// only accept JSON and XML
            $request->acceptContentTypes(array('json', 'xml'));

            return new Response();
        }
    }

Apify will render the error message according to the format of the request.

    class UsersController extends Controller
    {
        public function indexAction($request)
        {
            $request->acceptContentTypes(array('json', 'xml'));

        	$response = new Response();
            if (! $request->hasParam('api_key')) {
                throw new Exception('Missing parameter: api_key', Response::FORBIDDEN);
            }
            
            // paginate results
            $options = array(
            	'page'  => $request->getParam('page', 1),
            	'count' => 20,
            );
            $response->users = $this->getModel('User')->findAll($options);

            return $response;
        }
    }

HTTP Request

    GET /users.json

The above request will output something similar to:

    Status: 403 Forbidden
    Content-Type: application/json

    {
        "code": 403,
        "error": {
            "message": "Missing parameter: api_key",
            "type": "Exception"
        }
    }

HTTP Request

    GET /users.json?api_key=fbf72a3d919596b98172b87af6292b96

Response

    Status: 200 OK
    Content-Type: application/json

    {
        "code": 200,
        "data": {
            "users": [{
                "id": "1",
                "username": "matt",
                "email": "matt@email.com",
                "name": "Matt",
                "updated_at": "2011-07-25 21:06:37",
                "crated_at": "2011-07-25 20:30:11"
            }, {
                "id": "2",
                "username": "james",
                "email": "james@email.com",
                "name": "James",
                "updated_at": "2011-07-25 21:06:37",
                "crated_at": "2011-07-25 20:30:11"
            }]
        }
    }

### Resourceful Routes

Apify supports REST style URL mappings where you can map different HTTP methods, such as GET, POST, PUT and 
DELETE, to different actions in a controller. This basic REST design principle establishes a one-to-one mapping between create, read, update, and delete (CRUD) operations and HTTP methods:

    Method 	URL Path 	Action 		Used for
    -----------------------------------------------------------
    GET 	/users 		index 		display a list of all users
    GET 	/users/:id 	show 		display a specific user
    POST 	/users 		create 		create a new user
    PUT 	/users/:id 	update 		update a specific user
    DELETE 	/users/:id 	destroy 	delete a specific user

If you wish to enable RESTful mappings, add the following line to the index.php file:

    try {
        $request = new Request();
        $request->enableUrlRewriting();
        $request->enableRestfulMapping();
        $request->dispatch();
    } catch (Exception $e) {
        $request->catchException($e);
    }

The RESTful UsersController for the above mapping will contain 5 actions as follows:

    class UsersController extends Controller
    {
        public function indexAction($request) {}
        public function showAction($request) {}
        public function createAction($request) {}
        public function updateAction($request) {}
        public function destroyAction($request) {}
    }

By convention, each action should map to a particular CRUD operation in the database.

Some guidelines to avoid breaking API compatibility when you add new resources and parameters:

* Don't change resource URLs without a good reason.
* Don't let parameter positions matter.
* Accept and ignore unknown parameters.
