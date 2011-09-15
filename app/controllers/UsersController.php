<?php
class UsersController extends Controller
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
        if (! $request->hasContentType()) {
            $request->setContentType('json');
        }
        $request->acceptContentTypes(array('json', 'xml'));
    }
    
    /**
     * @route GET /?method=users
     * @route GET /users
     * 
     * @param Request $request
     * @return Response
     */
    public function indexAction($request)
    {
        $response = new Response();
        $response->users = $this->getModel('User')->findAll();
        
        return $response;
    }
    
    /**
     * @route GET /?method=users.show&id=1
     * @route GET /?method=users.show&id=matt
     * @route GET /users/1
     * @route GET /users/matt
     * 
     * @param Request $request
     * @return Response|Exception
     */
    public function showAction($request)
    {
        $model = $this->getModel('User');
        $id = $request->getParam('id');
        $user = is_numeric($id) ? $model->find($id) : $model->findBy(array('username'=>$id));
        if (! $user) {
            throw new Exception('User not found', Response::NOT_FOUND);
        }
        
        $response = new Response();
        $response->user = $user;
        
        return $response;
    }

    /**
     * @route POST /?method=users.create
     * @route POST /users/create
     * 
     * @param Request $request
     * @return Response|Exception
     */
    public function createAction($request)
    {
        if ('POST' != $request->getMethod()) {
            throw new Exception('HTTP method not allowed', Response::NOT_ALLOWED);
        }
        
        try {
            $user = new User(array(
                'name'     => $request->getPost('name'),
                'username' => $request->getPost('username'), 
                'email'    => $request->getPost('email'), 
                'gender'   => $request->getPost('gender')
            ));
        } catch (ValidationException $e) {
            throw new Exception($e->getMessage(), Response::OK);
        }
        
        $id = $this->getModel('User')->save($user);
        if (! is_numeric($id)) {
            throw new Exception('An error occurred while creating user', Response::OK);
        }
        
        $response = new Response();
        $response->setCode(Response::CREATED);
        $response->setEtagHeader(md5('/users/' . $id));
        
        return $response;
    }

    /**
     * @route POST /?method=users.update&id=1
     * @route POST /users/1/update
     * 
     * @param Request $request
     * @return Response|Exception
     */
    public function updateAction($request)
    {
        if ('POST' != $request->getMethod()) {
            throw new Exception('HTTP method not supported', Response::NOT_ALLOWED);
        }
        
        $id = $request->getParam('id');
        
        $model = $this->getModel('User');
        $user = $model->find($id);
        if (! $user) {
            throw new Exception('User not found', Response::NOT_FOUND);
        }
        
        try {
            $user->username = $request->getPost('username');            
        } catch (ValidationException $e) {
            throw new Exception($e->getMessage(), Response::OK);
        }
        $model->save($user);
        
        // return 200 OK
        return new Response();
    }
    
    /**
     * @route GET /?method=users.destroy&id=1
     * @route GET /users/1/destroy
     * 
     * @param Request $request
     * @return Response|Exception
     */
    public function destroyAction($request)
    {
        $id = $request->getParam('id');
        
        $model = $this->getModel('User');
        $user = $model->find($id);
        if (! $user) {
            throw new Exception('User not found', Response::NOT_FOUND);
        }
        $model->delete($user->id);
        
        // return 200 OK
        return new Response();
    }
}
