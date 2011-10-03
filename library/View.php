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
 * @package     View
 * @author      Federico Cargnelutti <federico@kewnode.com>
 * @copyright   Copyright (c) 2011 Kewnode Ltd.
 * @version     $Id: $
 */
class View
{
    /**
     * @var null|string
     */
    protected $script;
    
    /**
     * @var null|string
     */
    protected $scriptDir;
    
    /**
     * @var null|Layout
     */
    protected $layout;
    
    /**
     * @var stdClass
     */
    protected $vars;
    
    /**
     * @var array
     */
    protected $helpers = array();
    
    /**
     * Class constructor.
     * 
     * @param null|string $script
     */
    public function __construct($script = null)
    {
        $this->vars = new stdClass();
        if (isset($script)) {
            $this->setScript($script);
        }
    }
    
    /**
     * Assigns variables to the view script.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     * @throws RuntimeException
     */
    public function assign($key, $value)
    {
        if (! is_string($key)) {
            throw new RuntimeException(__METHOD__ . ': expects parameter 1 to be a string');
        }
        $this->vars->$key = $value;
        
        return $this;
    }
        
    /**
     * Proxy method for self::assign()
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->assign($key, $value);
    }
    
    /**
     * Support isset() overloading.
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->vars->$key);
    }
    
    /**
     * Support unset() overloading.
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->vars->$key);
    }
    
    /**
     * Magic function so that $this->value will work.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($key)
    {
        return $this->vars->$key;
    }
    
    /**
     * @return stdClass
     */
    public function getVars()
    {
        return $this->vars;
    }
    
    /**
     * @param string $name
     * @return self
     * @throws RuntimeException
     */ 
    public function setScript($name) 
    {
        if (false !== strpos($name, '/')) {
            $parts = explode('/', $name);
            if (! isset($parts[1])) {
                throw new RuntimeException(__METHOD__ . ' expects parameter 1 to be valid script name');
            }
            $this->scriptDir = $parts[0];
            $this->script = $parts[1];
        } else {
            $this->script = $name;
        }
        
        return $this;
    }
 
    /**
     * @return null|string
     */ 
    public function getScript() 
    {
        return $this->script;
    }
    
    /**
     * @param string $dir
     * @return self
     */ 
    public function setScriptDir($dir) 
    {
        $this->scriptDir = $dir;
        return $this;
    }
    
    /**
     * @return null|string
     */ 
    public function getScriptDir() 
    {
        return $this->scriptDir;
    }
    
    /**
     * @param Layout|string $layout
     * @return self
     */ 
    public function setLayout($layout) 
    {
        if ($layout instanceof Layout) {
            $this->layout = $layout;    
        } else {
            $this->setLayout(new Layout($layout));
        }
        return $this;
    }
 
    /**
     * @return null|Layout
     */ 
    public function getLayout() 
    {
        return $this->layout;
    }
    
    /**
     * Proxy for Layout::getPlaceholder() method.
     *
     * @return stdClass|null
     */
    public function layout()
    {
        return $this->getLayout()->getPlaceholder();
    }
    
    /**
     * @param object $obj
     * @return void
     * @throws RuntimeException
     */
    public function addHelper($obj)
    {
        if (! is_object($obj)) {
            throw new RuntimeException(__METHOD__ . ' expects parameter 1 to be an object');
        }
        $this->helpers[get_class($obj)] = $obj;
    }
    
    /**
     * @return string $name
     * @throws RuntimeException 
     */
    public function getHelper($name)
    {
        if (! array_key_exists($name, $this->helpers)) {
            throw new RuntimeException(sprintf('%s: helper "%s" not found', __METHOD__, $name));        
        }
        return $this->helpers[$name];
    }
}

/**
 * @category    Library
 * @package     View
 * @author      Federico Cargnelutti <federico@kewnode.com>
 * @copyright   Copyright (c) 2011 Kewnode Ltd.
 * @version     $Id: $
 */
class Layout
{
    /**
     * @var null|string
     */
    protected $script;
    
    /**
     * @var stdClass
     */
    protected $placeholder;
    
    /**
     * Class constructor.
     *
     * @param string $script
     * @return void
     */
    public function __construct($script)
    {
        $this->setScript($script);
        $this->setPlaceholder(new stdClass());
    }
    
    /**
     * Set script to use.
     * 
     * @param string $path 
     * @return void
     */ 
    public function setScript($script) 
    {
        $this->script = $script;
    }
 
    /**
     * Get current script.
     * 
     * @return string
     */ 
    public function getScript() 
    {
        return $this->script;
    }
    
    /**
     * Set placeholder object.
     * 
     * @param stdClass $obj
     * @return void
     */ 
    public function setPlaceholder(stdClass $obj) 
    {
        $this->placeholder = $obj;
    }
 
    /**
     * Get placeholder object.
     * 
     * @return stdClass
     */ 
    public function getPlaceholder() 
    {
        return $this->placeholder;
    }
    
    /**
     * Create setter and getter methods.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws RuntimeException
     */
    public function __call($method, $args)
    {        
        if (isset($args[0])) {
            $this->getPlaceholder()->$method = $args[0];
            return;
        } elseif (isset($this->getPlaceholder()->$method)) {
            return $this->getPlaceholder()->$method;
        }
        
        $m = 'Invalid method call: ' . get_class($this).'::'.$method.'()';
        throw new RuntimeException($m);
    }
}
