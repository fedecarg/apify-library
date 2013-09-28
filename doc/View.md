# View

## Building a Web Application

Apify supports routing arbitrary URLs to actions allowing you to quickly build web applications using non-resourceful routes.

Building a web application can be as simple as adding a few methods to your controller:

    class PostsController extends Controller
    {
        /** 
         * Route /posts/:id
         *
         * @param Request $request
         * @return View
         */
        public function showAction($request)
        {
            $id = $request->getParam('id');
            $post = $this->getModel('Post')->find($id); 
            if (! isset($post->id)) {
                return $request->redirect('/page-not-found');
            }
            
            $view = $this->initView();
            $view->post = $post;
            $view->user = $request->getSession()->user
            
            return $view;
        }
        
        /** 
         * Route /posts/create
         *
         * @param Request $request
         * @return View|null
         */
        public function createAction($request)
        {
            $view = $this->initView();
            if ('POST' !== $request->getMethod()) {
                return $view;
            }
            
            try {
                $post = new Post(array(
                    'title' => $request->getPost('title'), 
                    'text'  => $request->getPost('text')
                ));            
            } catch (ValidationException $e) {
                $view->error = $e->getMessage();
                return $view;
            }

            $id = $this->getModel('Post')->save($post);
            return $request->redirect('/posts/' . $id);
        }
    }

### Entity Class

You can add validation to your entity class to ensure that the values sent by the user are correct before saving them to the database:

    class Post extends Entity
    {
        protected $id;
        protected $title;
        protected $text;
        
        // sanitize and validate title (optional) 
        public function setTitle($value)
        {
            $value = htmlspecialchars(trim($value), ENT_QUOTES);
            if (empty($value) || strlen($value) < 3) {
                throw new ValidationException('Invalid title');
            }
            $this->title = $title;
        }
        
        // sanitize text (optional) 
        public function setText($value)
        {
            $this->text = htmlspecialchars(strip_tags($value), ENT_QUOTES);
        }
    }

### Routes

Apify provides a slimmed down version of the Zend Framework router:

    $routes[] = new Route('/posts/:id', 
        array(
            'controller' => 'posts',
            'action'     => 'show'
        ),
        array(
            'id'         => '\d+'
        )
    );
    $routes[] = new Route('/posts/create', 
        array(
            'controller' => 'posts', 
            'action'     => 'create'
        )
    );

HTTP Request

    GET /posts/1

Incoming requests are dispatched to the controller "Posts" and action "show".

