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
     * @return void
     * @throws RendererException
     */
    public function render(Request $request)
    {
        $response = $request->getResponse();
        
        $contentType = $request->getContentType();
        if ('json' === $contentType) {
            $renderer = new JsonRenderer();
        } else if ('xml' === $contentType) {
            $renderer = new XmlRenderer();
        } else if ('rss' === $contentType) {
            $renderer = new RssRenderer();
        } else if ('html' === $contentType) {
            $renderer = new HtmlRenderer();
        } else {
            $renderer = new HtmlRenderer();
            $e = new RendererException('Content type missing or invalid', Response::NOT_FOUND);
            $response->setException($e);     
        }
        
        $body = $renderer->render($request, $response);
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
        $view = $response->getView(); 
        if (! ($view instanceof View))  {
            throw new RuntimeException('No View object set; unable to render view');
        }
        
        $response->setContentTypeHeader('html');
        
        $dir = strtolower($request->getController());
        if (null !== $view->getScriptDir()) {
            $dir = $view->getScriptDir();
        }
        $filename = strtolower($request->getAction());
        if (null !== $view->getScript()) {
            $filename = $view->getScript();
        }
        
        $scriptFile = sprintf('%s/views/%s/%s.phtml', APP_DIR, $dir, $filename);
        $scriptFile = str_replace('/', DIRECTORY_SEPARATOR, $scriptFile);
        if (! file_exists($scriptFile)) {
            throw new RuntimeException('View script not found: ' . $scriptFile);
        }
        unset($dir, $filename);        
        
        ob_start();
        include $scriptFile;
        $body = ob_get_clean();
        
        if (null !== $view->getLayout()) {
            $layoutFile = sprintf('%s/views/layout/%s.phtml', APP_DIR, $view->getLayout()->getScript());
            $layoutFile = str_replace('/', DIRECTORY_SEPARATOR, $layoutFile);
            if (file_exists($layoutFile)) {
                $view->layout()->content = $body;
                unset($viewScript, $body);
                ob_start();
                include $layoutFile;
                $body = ob_get_clean();
            }
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
        if (isset($response->channel) && is_array($response->channel)) {
            foreach ($response->channel as $key => $value) {
                $body .= '    <' . $key . '>'  . htmlspecialchars($value) . '</' . $key . '>' . "\r\n";
            }
        }
        if (isset($response->items) && is_array($response->items)) {
            $body .= $this->serialize($response->items, 1);
        }
        $body .= '    </channel>' . "\r\n";
        $body .= '</rss>' . "\r\n";
        
        return $body; 
    }
}
