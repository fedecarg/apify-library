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
 * @package     Session
 * @author      Federico Cargnelutti <federico@kewnode.com>
 * @copyright   Copyright (c) 2011 Kewnode Ltd.
 * @version     $Id: $
 */
class Session
{
    /**
     * @var boolean
     */
    protected $isStarted = false;
    
    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->isStarted = session_id() == '' ? false : true;
        if (!$this->isStarted) {
            $this->start();
        }
    }
    
    /**
     * Creates a session or resumes the current one based on a session identifier.
     * 
     * @return boolean
     */
    public function start() 
    {
        if (!$this->isStarted) {
            $this->isStarted = session_start();
        }
        return $this->isStarted;
    }
    
    /**
     * Retrieves a value and return $default if there is no element set.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }
        return $default;
    }

    /**
     * Magic function so that $obj->value will work.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }
    
    /**
     * Adds an element onto the end of the array.
     *
     * @param string $key
     * @param mixed $value
     * @throws RuntimeException
     * @return void
     */
    public function __set($key, $value)
    {
        if (! is_string($key)) {
            throw new RuntimeException(__METHOD__ . ': expects parameter 1 to be a string');
        }
        $_SESSION[$key] = $value;
    }

    /**
     * Support isset() overloading.
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        unset($_SESSION[$key]); 
    }
    
    /**
     * Update the current session id with a newly generated one.
     * 
     * @return void
     */
    public function regenerateId() 
    {
        if ($this->isStarted) {
            session_regenerate_id();
            $this->destroy();
            $this->start();
        }
    }
    
    /**
     * Destroys all data registered to a session.
     * 
     * @return void
     * @throw RuntimeException
     */
    public function destroy()
    {
        if (!$this->isStarted) {
            throw new RuntimeException('Session not started. Unable to destroy data.');
        }
        
        $_SESSION = array();
        $this->expireCookie();
        session_destroy();
        
        $result = session_id() == '' ? false : true;
        $this->isStarted = $result;
    }
    
    /**
     * Expires the session cookie.
     * 
     * @return void
     */
    public function expireCookie()
    {
        if (isset($_COOKIE[session_name()])) {
            $cookieParams = session_get_cookie_params();
            setcookie(
                session_name(),
                false,
                315554400,
                $cookieParams['path'],
                $cookieParams['domain'],
                $cookieParams['secure']
            );
        }
    }
}