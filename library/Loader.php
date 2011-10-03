<?php
/**
 * Apify - Copyright (c) 2011, Kewnode Ltd. All rights reserved.
 * 
 * THIS COPYRIGHT INFORMATION MUST REMAIN INTACT AND MAY NOT BE MODIFIED IN ANY WAY.
 * 
 * THIS SOFTWARE IS PROVIDED BY KEWNODE LTD "AS IS" AND ANY EXPRESS OR IMPLIED 
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO 
 * EVENT SHALL KEWNODE LTD BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, 
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT 
 * OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, 
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY 
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Library
 * @package     Loader
 * @author      Federico Cargnelutti <federico@kewnode.com>
 * @copyright   Copyright (c) 2011 Kewnode Ltd.
 * @version     $Id: $
 */
class Loader
{
    /**
     * @var array $registry
     */
    private $registry = array();

    /**
     * @var Loader Singleton instance
     */
    private static $instance = null;
    
    /**
     * @return Loader
     */
    public static function getInstance()
    {
        if (null === self::$instance) {            
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * @param string $key
     * @return object
     */
    public function get($key)
    {
        if (! $this->isRegistered($key)) {
            $this->add(new $key());
        }
        return $this->registry[$key];
    }

    /**
     * @param object $obj
     * @param null|string $key
     * @return void
     * @throws RuntimeException
     */
    public function add($obj, $key = null)
    {
        if (! is_object($obj)) {
            throw new RuntimeException(__METHOD__ . ' expects parameter 1 to be an object');
        }
        
        $key = null !== $key ? $key : get_class($obj);
        $this->registry[$key] = $obj;
    }

    /**
     * @param string $key Class name
     * @return boolean
     */
    public function isRegistered($key)
    {
        return array_key_exists($key, $this->registry);
    }

    /**
     * @param string $name Controller name
     * @return object|null
     * @throws LoaderException
     */
    public function getController($name)
    {
        $className = $name . 'Controller';
        if (! $this->isRegistered($className)) {
            if (! $this->includeFile($className, 'controllers')) {
                $m = sprintf('Controller "%s" not found', $name);
                throw new LoaderException($m, Response::NOT_FOUND);
            }
            $this->add(new $className());
        }
        return $this->get($className);
    }

    /**
     * @param string $entityName
     * @return Model
     */
    public function getModel($entityName)
    {
        $modelName = $entityName . 'Model';
        if (! $this->isRegistered($modelName)) {
            if ($this->includeFile($modelName, 'models')) {
                $model = new $modelName($this->getDatabase());
            } else {
                $model = new Model($this->getDatabase());
            }
            
            $this->includeFile($entityName, 'models');
            $model->setEntity($entityName);
            
            if (null === $model->getTable()) {
                $tableName = $this->get('StringUtil')->underscore($entityName);
                $model->setTable($tableName);
            } 
            
            $this->add($model, $modelName);
        }
        return $this->get($modelName);
    }
    
    /**
     * @param string $name Service name
     * @return Service
     * @throws LoaderException
     */
    public function getService($name)
    {
        $className = $name . 'Service';
        if (! $this->isRegistered($className)) {
            if (! $this->includeFile($className, 'services')) {
                $m = sprintf('Service "%s" not found', $name);
                throw new LoaderException($m, Response::NOT_FOUND);
            } else {
                $obj = new $className();
            }
            $this->add($obj, $className);
        }
        return $this->get($className);
    }
    
    /**
     * @return Database
     * @throws LoaderException
     */
    public function getDatabase()
    {
        if (! $this->isRegistered('Database')) {
            try {
                $obj = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
            } catch (Exception $e) {
                throw new LoaderException($e->getMessage());
            }
            $this->add($obj);
        }
        return $this->get('Database');
    }
        
    /**
     * @param string $filename
     * @param string $dir
     * @return boolean
     * @throws RuntimeException
     */
    public function includeFile($filename, $dir)
    {
        if (preg_match('/[^a-z0-9\-_.]/i', $filename)) {
            throw new RuntimeException('Security check: Illegal character in filename.');
        }
        
        $file = str_replace('/', DIRECTORY_SEPARATOR, sprintf('%s/%s/%s.php', APP_DIR, $dir, $filename));
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        return false;
    }
    
    /**
     * Attempts to load the classes automatically.
     *
     * @param string $className
     * @return void
     * @throws RuntimeException
     */
    public static function autoload($className)
    {
        if (preg_match('/[^a-z0-9\-_.]/i', $className)) {
            throw new RuntimeException('Security check: Illegal character in filename.');
        }
        if (false !== strstr($className, '_')) {
            $className = str_replace('_', DIRECTORY_SEPARATOR, $className);
        }
        
        require_once $className . '.php';
    }
}
