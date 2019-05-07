<?php
namespace Rindow\Web\Http\Message;

class ResponseFactory
{
    static public function create()
    {
        $body = new Stream(fopen('php://temp', 'w+b'));
        return new Response(null,null,$body);
    }
}