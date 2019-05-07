<?php
namespace Rindow\Web\Http\Cookie;

class GenericCookieContextFactory implements CookieContextFactory
{
    public function create()
    {
    	$context = new GenericCookieManager();
    	return $context;
    }
}
