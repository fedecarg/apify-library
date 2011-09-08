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
 * @package     Renderer
 * @author      Federico Cargnelutti <federico@kewnode.com>
 * @copyright   Copyright (c) 2011 Kewnode Ltd.
 * @version     $Id: $
 */
class Renderer
{   
    /**
     * @param Request $request
     * @param Response $response
     * @return void
     * @throws RendererException
     */
    public function render(Request $request, Response $response)
    {
        $responseType = $response->getResponseType();
        $format = $response->getHttpAcceptHeader();
        if (null !== $format && $response->isAcceptableType($format)) {
            $responseType = $format;
        }
        
        if ('json' === $responseType) {
            $view = new JsonRenderer();
        } else if ('xml' === $responseType) {
            $view = new XmlRenderer();
        } else if ('rss' === $responseType) {
            $view = new RssRenderer();
        } else if ('html' === $responseType) {
            $view = new HtmlRenderer();
        } else {
            $view = new HtmlRenderer();
            $e = new RendererException('Content type missing or invalid', Response::NOT_FOUND);
            $response->setException($e);     
        }
        
        $body = $view->render($request, $response);
        $response->sendHeaders();
        
        exit($body);
    }
}


class HtmlRenderer
{
    /**
     * @param Request $request
     * @param Response $response
     * @return string
     * @throws RuntimeException
     */
    public function render(Request $request, Response $response)
    {
        $response->setContentTypeHeader('html');
        
        if (null !== $response->getException()) {
            $dir = 'error';
            $filename = DEBUG ? 'development' : 'production';
        } else {
            $dir = strtolower($request->getController());
            $filename = strtolower($request->getAction());
        }
        
        $viewScript = sprintf('%s/views/%s/%s.phtml', APP_DIR, $dir, $filename);
        $viewScript = str_replace('/', DIRECTORY_SEPARATOR, $viewScript);
        if (! file_exists($viewScript)) {
            throw new RuntimeException('View script not found: ' . $viewScript);
        }
        unset($dir, $filename);
        
        if ($response->getView() instanceof View)  {
            $view = $response->getView();
            ob_start();
            include $viewScript;
            $body = ob_get_clean();
            if (null !== $view->getLayout()) {
                $layoutScript = sprintf('%s/views/layout/%s.phtml', APP_DIR, $view->getLayout()->getScript());
                $layoutScript = str_replace('/', DIRECTORY_SEPARATOR, $layoutScript);
                if (file_exists($layoutScript)) {
                    $view->layout()->content = $body;
                    unset($viewScript, $body);
                    ob_start();
                    include $layoutScript;
                    $body = ob_get_clean();
                }
            } 
        } else {
            $view = $response->getData();
            ob_start();
            include $viewScript;
            $body = ob_get_clean();
        }
        
        return $body; 
    }
}

class JsonRenderer
{
    /**
     * @param Request $request
     * @param Response $response
     * @return string
     */
    public function render(Request $request, Response $response)
    {
        $body = json_encode($response->toArray());
        $callback = $request->getParam('jsonCallback', null);
        if (null !== $callback && is_string($callback)) {
            $callback = preg_replace('/[^a-z0-9_.]/i', '', $callback);
            $body = $callback . '(' . $body . ')';
            $response->setContentTypeHeader('js');
        } else {
            $response->setContentTypeHeader('json');
        }
        
        return $body;
    }
}

class XmlRenderer
{
    /**
     * @param Request $request
     * @param Response $response
     * @return string
     */
    public function render(Request $request, Response $response)
    {
        $response->setContentTypeHeader('xml');
        $data = $response->toArray();
        
        $body  = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
        $body .= '<response>' . "\r\n";
        $body .= $this->serialize($data, 1);
        $body .= '</response>' . "\r\n";
        
        return $body; 
    }
    
    /**
     * @param array $data
     * @param int $tabcount
     * @return string
     */
    public function serialize(array $data, $tabcount = 0)
    {
        $result = '';
        $tabs = '';
        for ($i = 0; $i < $tabcount; $i ++) {
            $tabs .= '    ';
        }
        
        foreach ($data as $key => $val) {
            $result .= $tabs;
            $key = is_int($key) ? 'item' : $key;
            $result .= '<' . $key . '>';
            if (is_object($val)) {
                $val = (array) $val;
            }
            if (! is_array($val)) {
                $result .= htmlspecialchars($val);
            } else {
                $result .= "\r\n";
                $result .= $this->serialize($val, $tabcount + 1, $key);
                $result .= $tabs;
            }
            $result .= '</' . $key . '>' . "\r\n";
        }
        
        return $result;
    }
}

class RssRenderer extends XmlRenderer
{
    /**
     * @param Request $request
     * @param Response $response
     * @return string
     */
    public function render(Request $request, Response $response)
    {
        $response->setContentTypeHeader('rss');
        $data = $response->toArray();

        $body  = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
        $body .= '<rss version="2.0">'  . "\r\n";
        $body .= '    <channel>' . "\r\n";
        if (isset($response->channel)) {
            foreach ($response->channel as $key => $value) {
                $body .= '    <' . $key . '>'  . htmlspecialchars($value) . '</' . $key . '>' . "\r\n";
            }
        }
        $body .= $this->serialize($response->items, 1);
        $body .= '    </channel>' . "\r\n";
        $body .= '</rss>' . "\r\n";
        
        return $body; 
    }
}
