<?php
class UsersController extends Apify_Controller
{
    /**
     * @route GET /?method=users
     * @route GET /users
     * 
     * @param Apify_Request $request
     * @return Apify_Response|View
     */
    public function indexAction($request)
    {
        // serve HTML, JSON and XML
        $request->acceptContentTypes(array('html', 'json', 'xml'));
        
        if ('html' == $request->getContentType()) {
            $response = new Apify_View();
            $response->setLayout('main');
        } else {
            $response = new Apify_Response();
        }
        
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
     * @return Response|View
     * @throws Exception
     */
    public function showAction($request)
    {
        // serve HTML, JSON and XML
        $request->acceptContentTypes(array('html', 'json', 'xml'));
        
        $model = $this->getModel('User');
        $id = $request->getParam('id');
        $user = is_numeric($id) ? $model->find($id) : $model->findBy(array('username'=>$id));
        if (! $user) {
            throw new Exception('User not found', Apify_Response::NOT_FOUND);
        }
        
        if ('html' == $request->getContentType()) {
            $response = new Apify_View();
            $response->setLayout('main');
        } else {
            $response = new Apify_Response();
            $response->setEtagHeader(md5('/users/' . $user->id));
        }
        
        $response->user = $user; 
        return $response;
    }

    /**
     * @route POST /?method=users.create&format=json
     * @route POST /users/create.json
     * 
     * @param Apify_Request $request
     * @return Apify_Response
     * @throws Exception
     */
    public function createAction($request)
    {
        $request->acceptContentTypes(array('json'));
        if ('POST' != $request->getMethod()) {
            throw new Exception('HTTP method not allowed', Apify_Response::NOT_ALLOWED);
        }
        
        try {
            $user = new User(array(
                'name'     => $request->getPost('name'),
                'username' => $request->getPost('username'), 
                'email'    => $request->getPost('email'), 
                'gender'   => $request->getPost('gender')
            ));
        } catch (Apify_ValidationException $e) {
            throw new Exception($e->getMessage(), Apify_Response::OK);
        }
        
        $id = $this->getModel('User')->save($user);
        if (! is_numeric($id)) {
            throw new Exception('An error occurred while creating user', Apify_Response::OK);
        }
        
        $response = new Apify_Response();
        $response->setCode(Apify_Response::CREATED);
        $response->setEtagHeader(md5('/users/' . $id));
        
        return $response;
    }

    /**
     * @route POST /?method=users.update&id=1&format=json
     * @route POST /users/1/update.json
     * 
     * @param Apify_Request $request
     * @return Apify_Response
     * @throws Exception
     */
    public function updateAction($request)
    {
        $request->acceptContentTypes(array('json'));
        if ('POST' != $request->getMethod()) {
            throw new Exception('HTTP method not supported', Apify_Response::NOT_ALLOWED);
        }        
        
        $id = $request->getParam('id');
        
        $model = $this->getModel('User');
        $user = $model->find($id);
        if (! $user) {
            throw new Exception('User not found', Apify_Response::NOT_FOUND);
        }
        
        try {
            $user->username = $request->getPost('username');            
        } catch (Apify_ValidationException $e) {
            throw new Exception($e->getMessage(), Apify_Response::OK);
        }
        $model->save($user);
        
        // return 200 OK
        return new Apify_Response();
    }
    
    /**
     * @route GET /?method=users.destroy&id=1&format=json
     * @route GET /users/1/destroy.json
     * 
     * @param Apify_Request $request
     * @return Apify_Response
     * @throws Exception
     */
    public function destroyAction($request)
    {
        $request->acceptContentTypes(array('json'));
        
        $id = $request->getParam('id');
        $model = $this->getModel('User');
        $user = $model->find($id);
        if (! $user) {
            throw new Exception('User not found', Apify_Response::NOT_FOUND);
        }
        $model->delete($user->id);
        
        // return 200 OK
        return new Apify_Response();
    }
}
