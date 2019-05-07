<?php
namespace Rindow\Web\Http\Message;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Rindow\Web\Http\Exception;
use ArrayObject;

/**
 * Representation of an incoming, server-side HTTP request.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 *
 * Additionally, it encapsulates all data as it has arrived to the
 * application from the CGI and/or PHP environment, including:
 *
 * - The values represented in $_SERVER.
 * - Any cookies provided (generally via $_COOKIE)
 * - Query string arguments (generally via $_GET, or as parsed via parse_str())
 * - Upload files, if any (as represented by $_FILES)
 * - Deserialized body parameters (generally from $_POST)
 *
 * $_SERVER values MUST be treated as immutable, as they represent application
 * state at the time of request; as such, no methods are provided to allow
 * modification of those values. The other values provide such methods, as they
 * can be restored from $_SERVER or the request body, and may need treatment
 * during the application (e.g., body parameters may be deserialized based on
 * content type).
 *
 * Additionally, this interface recognizes the utility of introspecting a
 * request to derive and match additional parameters (e.g., via URI path
 * matching, decrypting cookie values, deserializing non-form-encoded body
 * content, matching authorization headers to users, etc). These parameters
 * are stored in an "attributes" property.
 *
 * Requests are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    protected $serverParams = array();
    protected $cookieParams = array();
    protected $queryParams = array();
    protected $uploadedFiles = array();
    protected $parsedBody = array();
    protected $attributes = array();

    public function __construct(
        array $serverParams = null,
        array $parsedBody = null,
        array $uploadedParams = null,
        array $cookieParams = null,
        array $attributes = null,
        UriInterface $uri = null,
        $method=null,
        StreamInterface $body=null,
        array $headers=null,
        $version = null)
    {
        if($serverParams)
            $this->serverParams = $serverParams;
        if($cookieParams)
            $this->cookieParams = $cookieParams;
        if($uploadedParams)
            $this->uploadedFiles  = $this->createUploadedFiles($uploadedParams);
        if($parsedBody)
            $this->parsedBody  = $parsedBody;
        if($attributes)
            $this->attributes  = $attributes;
        if($uri===null)
            $uri = $this->createUri($serverParams);
        if($version===null)
            $version = $this->createProtocolVersion($serverParams);
        if($method===null)
            $method = $this->createMethod($serverParams);
        if($headers===null)
            $headers = $this->createHeaders($serverParams);
        $this->queryParams  = $this->createQueryParams($serverParams,$uri);
        parent::__construct($uri,$method,$body,$headers,$version);
    }

    protected function createUri($serverParams)
    {
        if(!isset($serverParams['REQUEST_URI']))
            return null;
        if(isset($serverParams['HTTPS'])) {
            $https = strtolower($serverParams['HTTPS']);
            $scheme = ($https=='on'||$https=='1')?'https':'http';
        } else {
            $scheme = 'http';
        }
        if(isset($serverParams['HTTP_HOST'])) {
            $hostPort = $serverParams['HTTP_HOST'];
        } else {
            if(isset($serverParams['SERVER_NAME']))
                $hostPort = $serverParams['SERVER_NAME'];
            else
                $hostPort = 'unknown-host';
            if(isset($serverParams['SERVER_PORT'])) {
                if($scheme=='http' && $serverParams['SERVER_PORT']!=80)
                    $hostPort .= (':'.$serverParams['SERVER_PORT']);
                elseif($scheme=='https' && $serverParams['SERVER_PORT']!=443)
                    $hostPort .= (':'.$serverParams['SERVER_PORT']);
            }
        }
        $uriString = $scheme.'://'.$hostPort.$serverParams['REQUEST_URI'];
        return new Uri($uriString);
    }

    protected function createProtocolVersion($serverParams)
    {
        if(!isset($serverParams['SERVER_PROTOCOL']))
            return null;
        if(strpos($serverParams['SERVER_PROTOCOL'],'HTTP/')!==0)
            return null;
        return substr($serverParams['SERVER_PROTOCOL'], 5);
    }

    protected function createMethod($serverParams)
    {
        if(!isset($serverParams['REQUEST_METHOD']))
            return null;
        return $serverParams['REQUEST_METHOD'];
    }

    protected function createHeaders($serverParams)
    {
        if(empty($serverParams))
            return array();
        $headers = array();
        foreach ($serverParams as $key => $value) {
            if(strpos($key, 'HTTP_')===0) {
                $name = str_replace('_', '-', strtolower(substr($key,5)));
                $headers[$name] = array($value);
            }
        }
        if(count($headers)==0)
            return null;
        return $headers;
    }

    public function createQueryParams($serverParams,$uri)
    {
        $query_string = null;
        if(isset($serverParams['QUERY_STRING'])) {
            $query_string = $serverParams['QUERY_STRING'];
        } elseif($uri) {
            $query_string = $uri->getQuery();
        }
        if(empty($query_string))
            return array();
        parse_str($query_string,$queryParams);
        if(empty($queryParams))
            return array();
        return $queryParams;
    }

    public function createUploadedFiles(array $uploadedParams)
    {
        $uploadedFiles = array();

        foreach ($uploadedParams as $fieldName => $property) {
            if (!$this->isFileProperty($property))
                throw new Exception\InvalidArgumentException('Invalid format of uploadedParams in '.$fieldName);
            $uploadedFiles[$fieldName] = $this->createUploadedFile($property,$fieldName);
        }

        return $uploadedFiles;
    }

    private function isFileProperty($property)
    {
        if(is_array($property) && isset($property['name']) && isset($property['type']) &&
            isset($property['tmp_name']) && isset($property['error']) && isset($property['size']))
            return true;
        return false;
    }

    private function createUploadedFile(array $property,$fieldName)
    {
        if (!is_array($property['name'])) {
            return new UploadedFile(
                $property['name'],
                $property['type'],
                $property['tmp_name'],
                $property['error'],
                $property['size']
            );
        }

        $uploadedFiles = array();
        foreach (array_keys($property['name']) as $dirname) {
            if(!array_key_exists($dirname, $property['name']))
                throw new Exception\InvalidArgumentException('Invalid "name" format of uploadedParams in "'.$fieldName.'"');
            if(!array_key_exists($dirname, $property['type']))
                throw new Exception\InvalidArgumentException('Invalid "type" format of uploadedParams in "'.$fieldName.'"');
            if(!array_key_exists($dirname, $property['tmp_name']))
                throw new Exception\InvalidArgumentException('Invalid "tmp_name" format of uploadedParams in "'.$fieldName.'"');
            if(!array_key_exists($dirname, $property['error']))
                throw new Exception\InvalidArgumentException('Invalid "error" format of uploadedParams in "'.$fieldName.'"');
            if(!array_key_exists($dirname, $property['size']))
                throw new Exception\InvalidArgumentException('Invalid "size" format of uploadedParams in "'.$fieldName.'"');

            $subtree = array(
                'name'     => $property['name'][$dirname],
                'type'     => $property['type'][$dirname],
                'tmp_name' => $property['tmp_name'][$dirname],
                'error'    => $property['error'][$dirname],
                'size'     => $property['size'][$dirname],
            );
            $uploadedFiles[$dirname] = $this->createUploadedFile($subtree,$dirname);
        }
        return $uploadedFiles;
    }

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * This method MUST NOT update the related Cookie header of the request
     * instance, nor related values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated cookie values.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $message = clone $this;
        $message->set('cookieParams',$cookies,$this->secret);
        return $message;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * These values SHOULD remain immutable over the course of the incoming
     * request. They MAY be injected during instantiation, such as from PHP's
     * $_GET superglobal, or MAY be derived from some other value such as the
     * URI. In cases where the arguments are parsed from the URI, the data
     * MUST be compatible with what PHP's parse_str() would return for
     * purposes of how duplicate query parameters are handled, and how nested
     * sets are handled.
     *
     * Setting query string arguments MUST NOT change the URI stored by the
     * request, nor the values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query string arguments.
     *
     * @param array $query Array of query string arguments, typically from
     *     $_GET.
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $message = clone $this;
        $message->set('queryParams',$query,$this->secret);
        return $message;
    }

    public function assertUploadedFiles($uploadedFiles)
    {
        foreach ($uploadedFiles as $file) {
            if(is_array($file)) {
                $this->assertUploadedFiles($file);
            } elseif($file instanceof UploadedFileInterface) {
                ;
            } else {
                throw new Exception\InvalidArgumentException('files must be array of UploadedFileInterface.:'.(is_object($file)? get_class($file) : gettype($file)));
            }
        }
    }

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     * @return static
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $this->assertUploadedFiles($uploadedFiles);
        $message = clone $this;
        $message->set('uploadedFiles',$uploadedFiles,$this->secret);
        return $message;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *     typically be in an array or object.
     * @return static
     * @throws \InvalidArgumentException if an unsupported argument type is
     *     provided.
     */
    public function withParsedBody($data)
    {
        $message = clone $this;
        $message->set('parsedBody',$data,$this->secret);
        return $message;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        if(!array_key_exists($name, $this->attributes))
            return $default;
        return $this->attributes[$name];
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $message = clone $this;
        $message->setToArray('attributes',$name,$value,$this->secret);
        return $message;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @return static
     */
    public function withoutAttribute($name)
    {
        $message = clone $this;
        $message->deleteFromArray('attributes',$name,$this->secret);
        return $message;
    }
}
