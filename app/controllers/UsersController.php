<?php
class UsersController extends Controller
{
    /**
     * @route GET /?method=users
     * @route GET /users
     * 
     * @param Request $request
     * @return Response
     */
    public function indexAction($request)
    {
        $model = $this->getModel('User');
        
        $response = new Response('html', array('json', 'xml'));
        $response->users = $model->findAll();
        
        return $response;
    }
    
    /**
     * @route GET /?method=users.show&id=1
     * @route GET /users/1/show
     * 
     * @param Request $request
     * @return Response
     */
    public function showAction($request)
    {
        $response = new Response('html', array('json', 'xml'));
        
        $model = $this->getModel('User');
        $id = $request->getParam('id');
        $user = is_numeric($id) ? $model->find($id) : $model->findBy(array('username'=>$id));
        if (! $user) {
            $e = new Exception('User not found', Response::NOT_FOUND);
            return $response->setException($e);
        }
        
        $response->user = $user;
        return $response;
    }

    /**
     * @route POST /?method=users.create
     * @route POST /users/create
     * 
     * @param Request $request
     * @return Response
     */
    public function createAction($request)
    {
        $response = new Response('json');
        if ('POST' != $this->getMethod()) {
            $e = new Exception('HTTP method not supported', Response::NOT_ALLOWED);
            return $response->setException($e);
        }
        
        $model = $this->getModel('User');
        $user = new User(array(
            'name'     => $request->getPost('name'),
            'username' => $request->getPost('username'), 
            'email'    => $request->getPost('email'), 
            'gender'   => $request->getPost('gender')
        ));
        
        $id = $model->save($user);
        if (! is_numeric($id)) {
            $e = new Exception('An error occurred while creating user', Response::OK);
            return $response->setException($e);
        }
        
        return $response;
    }

    /**
     * @route POST /?method=users.update&id=1
     * @route POST /users/1/update
     * 
     * @param Request $request
     * @return Response
     */
    public function updateAction($request)
    {   
        $response = new Response('json');
        if ('POST' != $this->getMethod()) {
            $e = new Exception('HTTP method not supported', Response::NOT_ALLOWED);
            return $response->setException($e);
        }
        
        $model = $this->getModel('User');
        $id = $request->getParam('id');
        $user = $model->find($id);
        if (! $user) {
            $e = new Exception('User not found', Response::NOT_FOUND);
            return $response->setException($e);
        }
        
        $user->username = $request->getPost('username');
        $model->save($user);
        
        $request->redirect(sprintf('/api/users/%s/show', $id));
    }
    
    /**
     * @route GET /?method=users.destroy&id=1
     * @route GET /users/1/destroy
     * 
     * @param Request $request
     * @return Response
     */
    public function destroyAction($request)
    {
        $response = new Response('json');
        
        $model = $this->getModel('User');
        $id = $request->getParam('id');
        $user = $model->find($id);
        if (! $user) {
            $e = new Exception('User not found', Response::NOT_FOUND);
            return $response->setException($e);
        }
        $model->delete($user->id);
        
        return $request->redirect('/api/users');
    }
}
