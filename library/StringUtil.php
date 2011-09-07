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
 * @package     Utilities
 * @author      Federico Cargnelutti <federico@kewnode.com>
 * @copyright   Copyright (c) 2011 Kewnode Ltd.
 * @version     $Id: $
 */
class StringUtil
{
    /**
     * Converts a string to CamelCase.
     * 
     * @param string $string
     * @return string
     */
    public function camelize($string)
    {
        return str_replace(' ', '', ucwords(preg_replace('/[^A-Z^a-z^0-9]+/', ' ', $string)));
    }

    /**
     * Creates an underscored and lowercase string.
     * 
     * @param string $string
     * @return string
     */
    public function underscore($string)
    {
        return strtolower(preg_replace('/(?!^)[[:upper:]]/', '_' . '\0', $string));
    }

    /**
     * Remove any not alphanumeric char from a string.
     *
     * @param string $string
     * @return string
     */
    public function filter($string, $regex = '')
    {
        return preg_replace('/[^a-z0-9-_\s' . $regex . ']/i', '', $string);
    }

    /**
     * Shorten a string using elipses
     *
     * @param string $string
     * @param string $length
     * @return string
     */
    public function shorten($string, $length)
    {
        if (strlen($string) > $length) {
            $string = preg_replace('/\s\S*$/', '...', substr($string, 0, $length - 3));
        }
        return $string;
    }

    /**
     * Sanitize string.
     *
     * @param string $string
     * @return string|null
     */
    public function sanitize($string)
    {
        return filter_var($string, FILTER_SANITIZE_STRING);
    }

    /**
     * Convet a string to search engine firendly (SEF).
     *
     * @param string $string
     * @return string
     */
    public function toSef($string)
    {
        $str = strtolower(trim($string));
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        return preg_replace('/-+/', "-", $str);
    }
}