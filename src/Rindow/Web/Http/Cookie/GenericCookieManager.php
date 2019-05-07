<?php
namespace Rindow\Web\Http\Cookie;

use Psr\Http\Message\ResponseInterface;
use Rindow\Web\Http\Exception;

class GenericCookieManager extends AbstractRepsonseModificator implements CookieContext
{
    protected $cookies = array();

    public function create($name,$value='',$expires=0,$path='',$domain='',$secure=false,$httponly=false)
    {
        $cookie = new GenericCookie($name,$value,$expires,$path,$domain,$secure,$httponly);
        return $cookie;
    }

    public function set($name,$value='',$expires=0,$path='',$domain='',$secure=false,$httponly=false)
    {
        if($name instanceof Cookie) {
            $cookie = $name;
            $name = $cookie->getName();
        } elseif(is_string($name)) {
            $cookie = $this->create($name,$value,$expires,$path,$domain,$secure,$httponly);
        } else {
            throw new Exception\InvalidArgumentException('"name" must be CookieInterface or String');
        }
        $this->cookies[$name] = $cookie;
    }

    public function get($name)
    {
        if(!isset($this->cookies[$name]))
            return null;
        return $this->cookies[$name];
    }

    public function getAll()
    {
        return $this->cookies;
    }

    public function delete($name)
    {
        unset($this->cookies[$name]);
    }

    public function clear()
    {
        $this->cookies = array();
    }

    public function addToResponse(ResponseInterface $response)
    {
        return $this->modifyHeaders($response,$this->cookies);
    }

    public function deleteFromResponse(ResponseInterface $response)
    {
        return $this->deleteHeaders($response,$this->cookies);
    }

    public function margeContext($cookieContext)
    {
        $cookies = $cookieContext->getAll();
        $this->cookies = array_replace($this->cookies,$cookies);
    }
}