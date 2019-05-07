<?php
namespace Rindow\Web\Http\Cookie;

use Psr\Http\Message\ResponseInterface;

interface ResponseModificator
{
    public function addToResponse(ResponseInterface $response);

    public function deleteFromResponse(ResponseInterface $response);
}
