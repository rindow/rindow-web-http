<?php
namespace Rindow\Web\Http\Message;

class ServerRequestFactory
{
    public static function factory($serviceLocator,$component,$args)
    {
        if(isset($args['testmode'])&&$args['testmode'])
            return self::factoryTestEnvironment($serviceLocator,$component,$args);
        return self::fromGlobals();
    }

    public static function fromGlobals(array $attributes=null,$input=null)
    {
        $serverParams   = $_SERVER;
        $parsedBody     = $_POST;
        $uploadedParams = $_FILES;
        $cookieParams   = $_COOKIE;
        if($input==null)
            $input = fopen('php://input', 'rb');
        $body = new Stream($input);

        return new ServerRequest(
            $serverParams,
            $parsedBody,
            $uploadedParams,
            $cookieParams,
            $attributes,
            null,
            null,
            $body);
    }

    public static function factoryTestEnvironment($serviceLocator,$component,$args)
    {
        if(!isset($args['testEnvironment']))
            throw new Exception\DomainException('environment is not specified');
        $env = $serviceLocator->get($args['testEnvironment']);
        return self::fromTestEnvironment($env);
    }

    public static function fromTestEnvironment($environment=null,array $attributes=null,$input=null)
    {
        if($environment==null)
            $environment = new TestModeEnvironment();
        $serverParams   = $environment->_SERVER;
        $parsedBody     = $environment->_POST;
        $uploadedParams = $environment->_FILES;
        $cookieParams   = $environment->_COOKIE;
        if($input==null)
            $input = $environment->input;
        if($input)
            $body = new Stream($input);
        else
            $body = null;
        return new ServerRequest(
            $serverParams,
            $parsedBody,
            $uploadedParams,
            $cookieParams,
            $attributes,
            null,
            null,
            $body);
    }
}