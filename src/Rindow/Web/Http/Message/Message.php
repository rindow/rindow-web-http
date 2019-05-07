<?php
namespace Rindow\Web\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Rindow\Web\Http\Exception;

/**
 * HTTP messages consist of requests from a client to a server and responses
 * from a server to a client. This interface defines the methods common to
 * each.
 *
 * Messages are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 *
 * @link http://www.ietf.org/rfc/rfc7230.txt
 * @link http://www.ietf.org/rfc/rfc7231.txt
 */
class Message implements MessageInterface
{
    protected static $validProtocolVersions = array(
        '1.0' => true,
        '1.1' => true,
        '2.0' => true,
    );

    protected $protocolVersion = '1.1';
    protected $headers = array();
    protected $body;
    protected $secret;

    public function __construct(StreamInterface $body=null,array $headers=null,$version=null, $secret=null)
    {
        if($secret!=null)
            $this->secret = $secret;
        else
            $this->secret = spl_object_hash($this);
        if($version) {
            $this->assertProtocolVersion($version);
            $this->protocolVersion = $version;
        }
        if($headers)
            $this->setHeaders($headers);
        if($body)
            $this->body = $body;
    }

    protected function setHeaders($headers)
    {
        $this->headers = array();
        foreach ($headers as $name => $values) {
            if(!is_array($values))
                $values = array($values);
            $key = strtolower($name);
            $this->headers[$key] = array('name'=>$name,'values'=>$values);
        }
    }

    /*
     * Compatibility access level of PHP5.3
     */ 
    public function setHeader($name,$value,$secret)
    {
        $this->assertAccess($secret);
        if(!is_array($value))
            $value = array($value);
        $key = strtolower($name);
        $this->headers[$key] = array('name'=>$name,'values'=>$value);
    }

    /*
     * Compatibility access level of PHP5.3
     */ 
    public function addHeader($name,$value,$secret)
    {
        $this->assertAccess($secret);
        if(is_string($value)) {
            $value = array($value);
        }
        $key = strtolower($name);
        foreach ($value as $v) {
            if(!array_key_exists($key, $this->headers))
                $this->headers[$key]['name'] = $name;
            $this->headers[$key]['values'][] = $v;
        }
    }

    /*
     * Compatibility access level of PHP5.3
     */ 
    public function deleteHeader($name,$secret)
    {
        $this->assertAccess($secret);
        $key = strtolower($name);
        unset($this->headers[$key]);
    }

    /*
     * Compatibility access level of PHP5.3
     */ 
    public function set($name,$value,$secret)
    {
        $this->assertAccess($secret);
        $this->$name = $value;
    }

    /*
     * Compatibility access level of PHP5.3
     */ 
    public function setToArray($name,$key,$value,$secret)
    {
        $this->assertAccess($secret);
        //$this->$name[$key] = $value;
        $array = $this->$name;
        $array[$key] = $value;
        $this->$name = $array;
    }

    /*
     * Compatibility access level of PHP5.3
     */ 
    public function deleteFromArray($name,$key,$secret)
    {
        $this->assertAccess($secret);
        //unset($this->$name[$key]);
        $array = $this->$name;
        unset($array[$key]);
        $this->$name = $array;
    }

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        $this->assertProtocolVersion($version);
        $message = clone $this;
        $message->set('protocolVersion',$version,$this->secret);
        return $message;
    }

    protected function assertAccess($secret)
    {
        if($this->secret!=$secret)
            throw new Exception\DomainException('operation not allowed.');
    }

    protected function assertProtocolVersion($version)
    {
        if(!is_string($version))
            throw new Exception\InvalidArgumentException('Invalid protocol version: '. (is_object($version)? get_class($version) : gettype($version)));
        if(!array_key_exists($version, self::$validProtocolVersions))
            throw new Exception\InvalidArgumentException('Invalid protocol version: '. $version);
    }

    protected function assertHeaderName($name)
    {
        $this->assertString($name,'Header name');
    }

    protected function assertString($string,$typeMessage)
    {
        if(!is_string($string))
            throw new Exception\InvalidArgumentException('Type of '.$typeMessage.' must be string.: '.(is_object($string)? get_class($string) : gettype($string)));
    }

    protected function assertValue($value)
    {
        if(is_string($value))
            return;
        if(!is_array($value))
            throw new Exception\InvalidArgumentException('Type of value must be string or array of string.: '.(is_object($value)? get_class($value) : gettype($value)));
        foreach ($value as $v) {
            if(!is_string($v))
                throw new Exception\InvalidArgumentException('Type of value must be string or array of string.: '.(is_object($v)? get_class($v) : gettype($v)));
        }
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders()
    {
        $headers = array();
        foreach ($this->headers as $header) {
            $key = $header['name'];
            $headers[$key] = $header['values'];
        }
        return $headers;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name)
    {
        $this->assertHeaderName($name);
        $key = strtolower($name);
        if(!array_key_exists($key, $this->headers))
            return false;
        return true;
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name)
    {
        if(!$this->hasHeader($name))
            return array();
        $key = strtolower($name);
        return $this->headers[$key]['values'];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name)
    {
        $value = $this->getHeader($name);
        if(count($value)==0)
            return '';
        return implode(',', $value);
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value)
    {
        $this->assertHeaderName($name);
        $this->assertValue($value);
        $message = clone $this;
        $message->setHeader($name,$value,$this->secret);
        return $message;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value)
    {
        $this->assertHeaderName($name);
        $this->assertValue($value);
        $message = clone $this;
        $message->addHeader($name,$value,$this->secret);
        return $message;
    }

    /**
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name Case-insensitive header field name to remove.
     * @return static
     */
    public function withoutHeader($name)
    {
        $this->assertHeaderName($name);
        $message = clone $this;
        $message->deleteHeader($name,$this->secret);
        return $message;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body Body.
     * @return static
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body)
    {
        $message = clone $this;
        $message->set('body',$body,$this->secret);
        return $message;
    }
}
