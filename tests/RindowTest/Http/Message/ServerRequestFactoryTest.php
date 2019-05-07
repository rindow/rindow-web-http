<?php
namespace RindowTest\Web\Http\Message\ServerRequestFactoryTest;

use PHPUnit\Framework\TestCase;
use Rindow\Web\Http\Message\ServerRequestFactory;
use Rindow\Web\Http\Message\TestModeEnvironment;
use Rindow\Stdlib\Dict;

class Test extends TestCase
{
    public function setUp()
    {
    }

	public function testNoneEnv()
	{
		$request = ServerRequestFactory::fromGlobals();
		$this->assertInstanceof('Rindow\Web\Http\Message\ServerRequest',$request);
	}

	public function testSetGlobal()
	{
		global $_SERVER;
        $backup_SERVER = $_SERVER;
		$_SERVER = array(
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 8080,
            'REQUEST_URI' => '/test.php?bar=boo',
            'REQUEST_METHOD' => 'POST',
            'SCRIPT_NAME' => '/test.php',
            'PHP_SELF' => '/test.php',
            'QUERY_STRING' => 'bar=boo',
            'HTTP_HOST' => 'localhost:8080',
            'HTTP_CONNECTION' => 'keep-alive',
            'CONTENT_LENGTH' => 7,
            'HTTP_CONTENT_LENGTH' => 7,
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_UPGRADE_INSECURE_REQUESTS' => 1,
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, sdch',
            'HTTP_ACCEPT_LANGUAGE' => 'ja,en-US;q=0.8,en;q=0.6',
        );

		global $_GET;
        $backup_GET = $_GET;
		$_GET = array(
            'bar' => 'boo',
		);

		global $_POST;
        $backup_POST = $_POST;
		$_POST = array(
            'foo' => 'test',
		);

		global $_FILES;
        $backup_FILES = $_FILES;
		$_FILES = array(
            'foo' => array(
                'name' => 'boo.gif',
                'type' => 'application/octet-stream',
                'tmp_name' => '/tmp/php4008.tmp',
                'error' => 0,
                'size' => 183,
            )
		);

		global $_COOKIE;
        $backup_COOKIE = $_COOKIE;
		$_COOKIE = array(
            'PHPSESSID' => 'l5rq77612ubr7cgajmf62tlvv3',
		);

		$attribute = array(
			'attr' => 'value',
		);

		$message = ServerRequestFactory::fromGlobals($attribute);
		$this->assertInstanceof('Rindow\Web\Http\Message\ServerRequest',$message);
		$this->assertEquals('http://localhost:8080/test.php?bar=boo',strval($message->getUri()));
		$this->assertEquals('bar=boo',strval($message->getUri()->getQuery()));
		$this->assertEquals(array('bar' => 'boo'),$message->getQueryParams());
		$this->assertEquals(array('foo' => 'test'),$message->getParsedBody());
		$this->assertEquals(array('PHPSESSID' => 'l5rq77612ubr7cgajmf62tlvv3'),$message->getCookieParams());
		$files = $message->getUploadedFiles();
		$this->assertEquals('boo.gif',$files['foo']->getClientFilename());
		$this->assertEquals('value',$message->getAttribute('attr'));
		$this->assertEquals('php://input',$message->getBody()->getMetadata('uri'));

        $_SERVER =  $backup_SERVER;
        $_GET    =  $backup_GET;
        $_POST   =  $backup_POST;
        $_FILES  =  $backup_FILES;
        $_COOKIE =  $backup_COOKIE;
	}

    public function testSetTestEnvironment()
    {
        $env = new TestModeEnvironment();

        $env->_SERVER = array(
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 8080,
            'REQUEST_URI' => '/test.php?bar=boo',
            'REQUEST_METHOD' => 'POST',
            'SCRIPT_NAME' => '/test.php',
            'PHP_SELF' => '/test.php',
            'QUERY_STRING' => 'bar=boo',
            'HTTP_HOST' => 'localhost:8080',
            'HTTP_CONNECTION' => 'keep-alive',
            'CONTENT_LENGTH' => 7,
            'HTTP_CONTENT_LENGTH' => 7,
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_UPGRADE_INSECURE_REQUESTS' => 1,
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, sdch',
            'HTTP_ACCEPT_LANGUAGE' => 'ja,en-US;q=0.8,en;q=0.6',
        );

        $env->_GET = array(
            'bar' => 'boo',
        );

        $env->_POST = array(
            'foo' => 'test',
        );

        $env->_FILES = array(
            'foo' => array(
                'name' => 'boo.gif',
                'type' => 'application/octet-stream',
                'tmp_name' => '/tmp/php4008.tmp',
                'error' => 0,
                'size' => 183,
            )
        );

        $env->_COOKIE = array(
            'PHPSESSID' => 'l5rq77612ubr7cgajmf62tlvv3',
        );

        $attribute = array(
            'attr' => 'value',
        );

        $message = ServerRequestFactory::fromTestEnvironment($env,$attribute);
        $this->assertInstanceof('Rindow\Web\Http\Message\ServerRequest',$message);
        $this->assertEquals('http://localhost:8080/test.php?bar=boo',strval($message->getUri()));
        $this->assertEquals('bar=boo',strval($message->getUri()->getQuery()));
        $this->assertEquals(array('bar' => 'boo'),$message->getQueryParams());
        $this->assertEquals(array('foo' => 'test'),$message->getParsedBody());
        $this->assertEquals(array('PHPSESSID' => 'l5rq77612ubr7cgajmf62tlvv3'),$message->getCookieParams());
        $files = $message->getUploadedFiles();
        $this->assertEquals('boo.gif',$files['foo']->getClientFilename());
        $this->assertEquals('value',$message->getAttribute('attr'));
        $this->assertNull($message->getBody());
    }
}