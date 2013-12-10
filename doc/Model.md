# Model

## The Domain Object Model

The model has a central position in a web application. It’s the domain-specific representation of the information on which the application operates.

An Entity instance represents a row in the database. In the example below, "User" is an entity class which maps object properties to database table fields. For example:

    class User extends Entity 
    {
        protected $id;
        protected $username;
        protected $email;
        protected $password;
        protected $name;
        protected $created_at; // automatically update
        protected $updated_at; // automatically update
    }

Properties are defined as protected. If you add a "created_at" or "updated_at" property, they are automatically updated for you by Apify. When you want to access a property you can just write:

    $user->email = 'me@email.com';
    $email = $user->email;

You can define the getter and setter methods yourself. If a method exists Apify will use the existing accessors.

    class User extends Entity 
    {
        protected $id;
        protected $email;
        
        public function setEmail($email)
        {
            $this->email = $email;
        }
    }

By default, the entity will be persisted to a table with the same name as the class name ("user"). In order to change that, you can call the setTable() method:

    class UsersController extends Controller
    {
        public function showAction($request)
        {
            $id = $request->getParam('id');
            
            // map the entity "User" to the table "users"
            $model = $this->getModel('User')->setTable('users');
            
            $response = new Response();
            $response->user = $model->find($id);
            
            return $response;
        }
    }

Or you can create a Model class and overwrite the $table property as follows:

    class UserModel extends Model
    {
        // user-defined table
        protected $table = 'users';
        
        // user-defined method
        public function findByUsername($username)
        {
            // ...
        }
    }

    class UsersController extends Controller
    {
        public function showAction($request)
        {
            $username = $request->getParam('username');

            $response = new Response();
            $response->user = $this->getModel('User')->findByUsername($username);
            
            return $response;
        }
    }

### Validating Entity Classes

Validating data before you send updates to the underlying database is a good practice that reduces errors:

    class User extends Entity 
    {
        protected $id;
        protected $email;
        
        // validate and sanitize input (optional)
        public function setEmail($value)
        {
            if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new ValidationException('Invalid email address');
            }
            $this->email = trim($value);
        }
    }

Because the validation is performed inside the class an exception is thrown if the value causes validation to fail. You can implement error handling for the code in your controller:

    class UsersController extends Controller
    {    
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
                $user->email = $request->getPost('email'); // throws ValidationException
                $model->save($user); // throws ModelException
            } catch (ValidationException $e) {
                // do something
            } catch (ModelException $e) {
                // do something
            }
            
            return new Response();
        }
    }

## Basic CRUD Operations

Apify allows you to perform some basic CRUD (create, read, update and delete) operations.

### Create

To create a domain class use the new operator, set its properties and call save:

    $user = new User(array(
        'name'  => 'James',
        'email' => 'james@gmail.com'
    ));

    // returns the user id or false
    $id = $this->getModel('User')->save($user);

The save method will persist your class to the database using the underlying database layer.

### Read

Retrieve a specific user:

// returns a new instance of User
    $user = $model->find(1); 

    // or...
    $user = $model->findBy(array(
        'email' => 'james@gmail.com', 
        'name'  => 'James'
    ));

Retrieve a list of users:

    // returns a collection of anonymous objects
    $users = $model->findAll();

    $users = $model->findAllBy(array('name'=>'James'));

    $options = array(
        'sort'  => 'name', 
        'order' => 'ASC'
    );
    $users = $model->findAllBy(array('name'=>'James'), $options));

Paginate list:

    $options = array(
        'page'  => 1,
        'count' => 20,
    );
    $users = $model->findAll($options);
    $pages = $model->paginate($options);

Pages object structure:

    Object ( 
        [pagesInRange] => Array (
            [0] => 1
            [1] => 2
        ) 
        [pageCount] => 2 
        [itemCountPerPage] => 20 
        [current] => 1 
        [first] => 1 
        [last] => 2 
    )

### Update

To update an instance, set some properties and then simply call save again:

    $user = $model->find(1);
    $user->name = 'Jimmy';
    $model->save($user);

### Delete

To delete an instance use the delete method:

    $model->delete(1);
