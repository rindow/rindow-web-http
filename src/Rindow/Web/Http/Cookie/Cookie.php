<?php
namespace Rindow\Web\Http\Cookie;

interface Cookie extends ResponseModificator
{
    public function getName();
    public function setValue($value);
    public function getValue();
    public function setExpires($expires);
    public function getExpires();
    public function setPath($path);
    public function getPath();
    public function setDomain($domain);
    public function getDomain();
    public function setSecure($secure);
    public function getSecure();
    public function setHttponly($httponly);
    public function getHttponly();
}
