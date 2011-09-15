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
 * @package     Response
 * @author      Federico Cargnelutti <federico@kewnode.com>
 * @copyright   Copyright (c) 2011 Kewnode Ltd.
 * @version     $Id: $
 */
class Response
{
    const OK                  = 200; // The request has succeeded.
    const CREATED             = 201; // The request has been fulfilled and resulted in a new resource being created.
    const NO_CONTENT          = 204; // The server has fulfilled the request but does not need to return an entity-body.
    const MOVED_PERM          = 301;
    const FOUND               = 302;
    const NOT_MODIFIED        = 304;
    const TEMP_REDERICT       = 307;
    const BAD_REQUEST         = 400; // The request could not be understood by the server due to malformed syntax.
    const UNAUTHORIZED        = 401; // The request requires user authentication.
    const FORBIDDEN           = 403; // The server understood the request, but is refusing to fulfill it.
    const NOT_FOUND           = 404; // The server has not found anything matching the Request-URI
    const NOT_ALLOWED         = 405; // The method specified in the Request-Line is not allowed for the resource identified by the Request-URI
    const NOT_ACCEPTABLE      = 406; // The server can only generate a response that is not accepted by the client 
    const REQUEST_TIMEOUT     = 408;
    const SERVER_ERROR        = 500;
    const NOT_IMPLEMENTED     = 501; // The server does not support the functionality required to fulfill the request.
    const UNAVAILABLE         = 503;
    const TIMEOUT             = 504;    
    
    /**
     * @var array
     */
    protected $statusCodes = array(
        self::OK              => 'HTTP/1.1 200 OK',
        self::CREATED         => 'HTTP/1.1 201 Created',
        self::NO_CONTENT      => 'HTTP/1.1 204 No Content',
        self::MOVED_PERM      => 'HTTP/1.1 301 Moved Permanently',
        self::FOUND           => 'HTTP/1.1 302 Found',
        self::NOT_MODIFIED    => 'HTTP/1.1 304 Not Modified',
        self::TEMP_REDERICT   => 'HTTP/1.1 307 Temporary Redirect',
        self::BAD_REQUEST     => 'HTTP/1.1 400 Bad Request',
        self::UNAUTHORIZED    => 'HTTP/1.1 401 Unauthorized',
        self::FORBIDDEN       => 'HTTP/1.1 403 Forbidden',
        self::NOT_FOUND       => 'HTTP/1.1 404 Not Found',
        self::NOT_ALLOWED     => 'HTTP/1.1 405 Method Not Allowed',
        self::NOT_ACCEPTABLE  => 'HTTP/1.1 406 Not Acceptable',
        self::REQUEST_TIMEOUT => 'HTTP/1.1 408 Request Timeout',
        self::SERVER_ERROR    => 'HTTP/1.1 500 Internal Server Error',
        self::NOT_IMPLEMENTED => 'HTTP/1.1 501 Not Implemented',
        self::UNAVAILABLE     => 'HTTP/1.1 503 Service Unavailable',
        self::TIMEOUT         => 'HTTP/1.1 504 Gateway Timeout'
    );
    
    /**
     * @var int
     */
    protected $code = self::OK;

    /**
     * @var array
     */
    public $mimeTypes = array(
        'html' => 'text/html',
        'json' => 'application/json',
        'js'   => 'application/javascript',
        'xml'  => 'application/xml',
        'rss'  => 'application/rss+xml',
        'atom' => 'application/atom+xml',
        'js'   => 'application/javascript',
        'txt'  => 'text/plain',
        'css'  => 'text/css'
    );

    /**
     * @var array
     */
    protected $headers = array();
    
    /**
     * @var null|stdClass
     */
    protected $error;
    
    /**
     * @var null|stdClass
     */
    protected $data;
    
    /**
     * @var null|View
     */
    protected $view;
    
    /**
     * @var null|Exception
     */
    protected $exception;

    /**
     * @param int $code
     * @return Response
     */
    public function setCode($code)
    {
        $this->code = $code;            
        return $this;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }
    
    /**
     * @param string $message
     * @param string $type
     * @return Response
     */
    public function setError($message, $type)
    {
        $error = new stdClass();
        $error->message = $message;
        $error->type = $type;      
          
        $this->error = $error;
        return $this;
    }

    /**
     * @return stdClass
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param Exception $e
     * @return Response
     * @throws Exception
     */
    public function setException(Exception $e)
    {
        $code = $e->getCode();
        if (0 === $code) {
            // status code is undefined
            $code = self::OK;
        } else if (! array_key_exists($code, $this->statusCodes)) {
            // unknown status code
            throw $e;
        }
        
        $this->setCode($code);
        $this->setError($e->getMessage(), get_class($e));
        
        $this->exception = $e;
        return $this;
    }

    /**
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
    }
        
    /**
     * @param null|int $seconds 3600 seconds = 1 hour
     * @return void
     */
    public function setCacheHeader($seconds = null) 
    {
        if (is_numeric($seconds)) {
            $this->addHeader('Cache-Control: max-age=' . $seconds . ', must-revalidate');
        } else {
            $this->addHeader('Cache-Control: no-cache, must-revalidate');
            $this->addHeader('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        }
    }
    
    /**
     * @param string $value
     * @return void
     */
    public function setEtagHeader($value)
    {
        $this->addHeader(sprintf('ETag: "%s"', $value));
    }
    
    /**
     * @param int $limit Number of requests allowed
     * @param int $remaining Number of requests remaining
     * @return void
     */
    public function setRateLimitHeader($limit, $remaining)
    {
        $this->addHeader('X-RateLimit-Limit: ' . $limit);
        $this->addHeader('X-RateLimit-Remaining: ' . $remaining);
    }
    
    /**
     * @param string $type
     * @return Response
     * @throws RuntimeException
     */
    public function setContentTypeHeader($type)
    {
        if (! array_key_exists($type, $this->mimeTypes)) {
            throw new RuntimeException('Invalid Content-Type header field');
        }
        
        $this->addHeader('Content-type: ' . $this->mimeTypes[$type]);
        return $this;
    }
    
    /**
     * @param string $string
     * @return void
     */
    public function addHeader($string)
    {
        $this->headers[] = $string;
    }
    
    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * @return void
     */
    public function sendHeaders()
    {
        $this->addHeader($this->statusCodes[$this->code]);
        $this->addHeader('Status: ' . $this->code);
        
        $headers = $this->getHeaders();
        for ($i = 0; $i < count($headers); $i++) {
            header($headers[$i]);
        }
    }
    
    /**
     * @param string $subType
     * @param string $mimeType
     * @return void
     */
    public function addMimeType($subType, $mimeType)
    {
        $this->mimeTypes[$subType] = $mimeType;
    }
    
    /**
     * @return array
     */
    public function getMimeTypes()
    {
        return $this->mimeTypes;
    }
    
    /**
     * @param View $view
     * @return self
     */ 
    public function setView($view) 
    {
        $this->view = $view;
        return $this;
    }
 
    /**
     * @return null|View
     */ 
    public function getView() 
    {
        return $this->view;
    }
    
    /**
     * @param string $key
     * @param mixed $value
     * @return Response
     * @throws RuntimeException
     */
    public function assign($key, $value)
    {
        if (! is_string($key)) {
            throw new RuntimeException(__METHOD__ . ': expects parameter 1 to be a string');
        } else if (null === $this->data) {
            $this->data = new stdClass();
        }
        
        if (is_object($value) && $value instanceof Entity) {
            // use dynamic properties
            $this->data->$key = $value->toObject();
        } else {
            $this->data->$key = $value;
        }
        
        return $this;
    }
    
    /**
     * @param stdClass $data
     * @return void
     */
    public function setData(stdClass $data)
    {
        $this->data = $data;
    }
    
    /**
     * @return stdClass
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * @return void
     */
    public function clearData()
    {
        $this->data = null;
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
        return isset($this->data->$key);
    }
    
    /**
     * Support unset() overloading.
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->data->$key);
    }
    
    /**
     * Magic function so that $this->value will work.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($key)
    {
        return $this->data->$key;
    }
    
    /**
     * @return array
     */
    public function toArray()
    {
        $a['code'] = $this->getCode();
        if (null !== $this->getError()) {
            $a['error'] = $this->getError();
        }
        if (null !== $this->getData()) {
            $a['data'] = $this->getData();
        }
        
        return $a;
    }
}
