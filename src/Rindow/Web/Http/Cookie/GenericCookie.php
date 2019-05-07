<?php
namespace Rindow\Web\Http\Cookie;

use Psr\Http\Message\ResponseInterface;
use DateTimeZone;
use DateTime;

class GenericCookie extends AbstractRepsonseModificator implements Cookie
{
    protected $name;
    protected $value;
    protected $expires;
    protected $path;
    protected $domain;
    protected $secure;
    protected $httponly;

    public function __construct(
        $name,$value='',$expires=0,$path='',$domain='',$secure=false,$httponly=false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->expires = $expires;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httponly = $httponly;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    public function getExpires()
    {
        return $this->expires;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function setSecure($secure)
    {
        $this->secure = $secure;
    }

    public function getSecure()
    {
        return $this->secure;
    }

    public function setHttponly($httponly)
    {
        $this->httponly = $httponly;
    }

    public function getHttponly()
    {
        return $this->httponly;
    }

    public function toString()
    {
        if(empty($this->value))
            return $this->toDeletedString();
        $header = $this->name.'='.$this->value;
        if(!empty($this->expires))
            $header .= '; Expires='.$this->getDateSting($this->expires);
        if(!empty($this->path))
            $header .= '; Path='.$this->path;
        if(!empty($this->domain))
            $header .= '; Domain='.$this->domain;
        if(!empty($this->secure))
            $header .= '; Secure';
        if(!empty($this->httponly))
            $header .= '; HttpOnly';
        return $header;
    }

    protected function toDeletedString()
    {
        $header = $this->name.'=deleted';
        $header .= '; Expires='.$this->getDateSting(0);
        if(!empty($this->path))
            $header .= '; Path='.$this->path;
        if(!empty($this->domain))
            $header .= '; Domain='.$this->domain;
        return $header;
    }

    protected function getDateSting($time)
    {   
        $timeZone = new DateTimeZone('GMT');
        $datetime = new DateTime('now',$timeZone);
        $datetime->setTimestamp($time);
        return $datetime->format(DATE_COOKIE);
    }

    public function addToResponse(ResponseInterface $response)
    {
        return $this->modifyHeaders($response,array($this));
    }

    public function deleteFromResponse(ResponseInterface $response)
    {
        return $this->deleteHeaders($response,array($this));
    }
}