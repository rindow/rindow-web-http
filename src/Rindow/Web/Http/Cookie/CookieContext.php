<?php
namespace Rindow\Web\Http\Cookie;

interface CookieContext extends ResponseModificator
{
    public function create($name,$value='',$expires=0,$path='',$domain='',$secure=false,$httponly=false);

    public function set($name,$value='',$expires=0,$path='',$domain='',$secure=false,$httponly=false);

    public function get($name);

    public function getAll();

    public function delete($name);

    public function clear();

    public function margeContext(/*CookieContext*/$cookieContext);
}
