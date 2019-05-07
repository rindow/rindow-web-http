<?php
namespace Rindow\Web\Http\Message;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Rindow\Web\Http\Exception;
use Rindow\Web\Http\Cookie\CookieInterface;

/**
 * Representation of an outgoing, server-side response.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - Status code and reason phrase
 * - Headers
 * - Message body
 *
 * Responses are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 */
class Response extends Message implements ResponseInterface
{
    protected static $reasons = array(
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated
        307 => 'Temporary Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    );

    /*
     * ** CAUTION ***
     * Compatibility access level "public" to PHP5.3
     */ 
    protected $statusCode = 200;
    protected $reasonPhrase = '';
    protected $cookies = array();

    public function __construct(
        $statusCode = null,
        $reasonPhrase=null,
        StreamInterface $body=null,
        array $headers=null,
        $version = null)
    {
        if($statusCode) {
            $this->assertStatusCode($statusCode);
            $this->statusCode = $statusCode;
        }
        if($reasonPhrase) {
            $this->assertString($reasonPhrase,'reason phrase');
            $this->reasonPhrase = $reasonPhrase;
        }
        parent::__construct($body,$headers,$version);
    }

    public function assertStatusCode($code)
    {
        if(!is_numeric($code) && !is_string($code))
            throw new Exception\InvalidArgumentException('Invalid status code: '. (is_object($code)? get_class($code) : gettype($code)));
        if(!array_key_exists($code, self::$reasons))
            throw new Exception\InvalidArgumentException('Invalid status code: '. $code);
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return static
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $this->assertStatusCode($code);
        $this->assertString($reasonPhrase,'reason phrase');
        $message = clone $this;
        $message->set('statusCode',intval($code),$this->secret);
        $message->set('reasonPhrase',empty($reasonPhrase) ? '' : $reasonPhrase,$this->secret);
        return $message;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase()
    {
        if(!empty($this->reasonPhrase))
            return $this->reasonPhrase;
        return self::$reasons[$this->statusCode];
    }

    ///**
    // * This is not the Psr7 standard.
    // * Helpful function for Cookie Header in response headers.
    // * Set and replace and delete a cookie.
    // */
    //public function withCookie(CookieInterface $cookie)
    //{
    //    $message = clone $this;
    //    $message->setCookie($cookie->getName(),$cookie,$this->secret);
    //    return $message;
    //}

    ///**
    // * This is not the Psr7 standard.
    // * Helpful function for Cookie Header in response headers.
    // * delete a cookie.
    // */
    //public function withoutCookie($name)
    //{
    //    $message = clone $this;
    //    $message->setCookie($name,null);
    //    return $message;
    //}

    ///**
    // * This is not the Psr7 standard.
    // * Helpful function for Cookie Header in response headers.
    // * Get cookie.
    // */
    //public function getCookie($name)
    //{
    //    if(!isset($this->cookies[$name]))
    //        return null;
    //    return $this->cookies[$name];
    //}

    ///**
    // * This is not the Psr7 standard.
    // * Helpful function for Cookie Header in response headers.
    // * Get cookie.
    // */
    //public function getCookies()
    //{
    //    return $this->cookies;
    //}

    ///*
    // * Compatibility access level of PHP5.3
    // */ 
    //public function setCookie($name,$cookie,$secret)
    //{
    //    $this->assertAccess($secret);
    //    $this->assertString($name,'Cookie name');
    //    if($cookie)
    //        $this->cookies[$name] = $cookie;
    //    else
    //        unset($this->cookies[$name]);
    //}
}
