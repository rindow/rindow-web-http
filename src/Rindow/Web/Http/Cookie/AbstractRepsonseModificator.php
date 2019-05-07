<?php
namespace Rindow\Web\Http\Cookie;

use Psr\Http\Message\ResponseInterface;

abstract class AbstractRepsonseModificator
{
    const COOKIE_HEADER_NAME = 'Set-Cookie';

    protected function modifyHeaders(ResponseInterface $response,array $cookies)
    {
        $headers = $response->getHeader(self::COOKIE_HEADER_NAME);

        $keys = array();
        foreach ($cookies as $cookie) {
            $keys[$cookie->getName().'='] = $cookie;
        }
        $newHeaders = array();
        $found = array();
        foreach($headers as $header) {
            foreach ($keys as $key => $cookie) {
                // NAME=VALUE; expires=DATE; path=PATH; domain=DOMAIN_NAME; secure';
                if(strpos($header, $key)===0) {
                    $newHeaders[] = $cookie->toString();
                    $found[$cookie->getName()] = true;
                } else {
                    $newHeaders[] = $header;
                }
            }
        }
        foreach ($keys as $cookie) {
            if(!isset($found[$cookie->getName()]))
                $newHeaders[] = $cookie->toString();
        }

        return $response->withHeader(self::COOKIE_HEADER_NAME, $newHeaders);
    }

    protected function deleteHeaders(ResponseInterface $response,array $cookies)
    {
        $headers = $response->getHeader(self::COOKIE_HEADER_NAME);

        $keys = array();
        foreach ($cookies as $cookie) {
            $keys[$cookie->getName().'='] = $cookie;
        }
        $newHeaders = array();
        foreach($headers as $header) {
            $found = false;
            foreach ($keys as $key => $cookie) {
                if(strpos($header, $key)===0) {
                    $found = true;
                    break;
                }
            }
            if(!$found)
                $newHeaders[] = $header;
        }

        return $response->withHeader(self::COOKIE_HEADER_NAME, $newHeaders);
    }
}
