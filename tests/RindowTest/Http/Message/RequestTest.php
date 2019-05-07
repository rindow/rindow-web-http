<?php
namespace RindowTest\Web\Http\Message\RequestTest;

use PHPUnit\Framework\TestCase;
use Rindow\Web\Http\Message\Request;
use Rindow\Web\Http\Message\Stream;
use Rindow\Web\Http\Message\Uri;

class Test extends TestCase
{
    public function testUri()
    {
        $message = new Request();
        $this->assertEquals('/',$message->getRequestTarget());
        $uri = new Uri('http://foouser@localhost:12345/boopath?bar=boo#foofragment');
        $new = $message->withUri($uri);
        $this->assertNull($message->getUri());
        $this->assertEquals(spl_object_hash($uri),spl_object_hash($new->getUri()));
        $this->assertEquals('/',$message->getRequestTarget());
        $this->assertEquals('/boopath?bar=boo',$new->getRequestTarget());
        $this->assertEquals(array(),$message->getHeader('Host'));
        $this->assertEquals(array('localhost:12345'),$new->getHeader('Host'));

        // clone
        $uri = new Uri('http://foouser@localhost:12345/boopath?bar=boo#foofragment');
        $body = new Stream();
        $headers = array('foo'=>array('bar'));
        $message = new Request($uri,'POST',$body,$headers,'2.0');
        $newUri = new Uri();
        $new = $message->withUri($newUri);
        $this->assertEquals('/boopath?bar=boo',$message->getRequestTarget());
        $this->assertEquals('/',$new->getRequestTarget());
        $this->assertEquals('POST',$message->getMethod());
        $this->assertEquals('POST',$new->getMethod());
        $this->assertEquals(spl_object_hash($body),spl_object_hash($message->getBody()));
        $this->assertEquals(spl_object_hash($body),spl_object_hash($new->getBody()));
        $this->assertEquals($headers,$message->getHeaders());
        $this->assertEquals($headers,$new->getHeaders());
        $this->assertEquals('2.0',$message->getProtocolVersion());
        $this->assertEquals('2.0',$new->getProtocolVersion());
    }

    public function testRequestTarget()
    {
        $message = new Request();
        $this->assertEquals('/',$message->getRequestTarget());
        $new = $message->withRequestTarget('/foo');
        $this->assertEquals('/',$message->getRequestTarget());
        $this->assertEquals('/foo',$new->getRequestTarget());

        // clone
        $uri = new Uri('http://foouser@localhost:12345/boopath?bar=boo#foofragment');
        $body = new Stream();
        $headers = array('foo'=>array('bar'));
        $message = new Request($uri,'POST',$body,$headers,'2.0');
        $newUri = new Uri();
        $new = $message->withRequestTarget('/foo');
        $this->assertEquals('/boopath?bar=boo',$message->getRequestTarget());
        $this->assertEquals('/foo',$new->getRequestTarget());
        $this->assertEquals(spl_object_hash($uri),spl_object_hash($message->getUri()));
        $this->assertEquals(spl_object_hash($uri),spl_object_hash($new->getUri()));
        $this->assertEquals('POST',$message->getMethod());
        $this->assertEquals('POST',$new->getMethod());
        $this->assertEquals(spl_object_hash($body),spl_object_hash($message->getBody()));
        $this->assertEquals(spl_object_hash($body),spl_object_hash($new->getBody()));
        $this->assertEquals($headers,$message->getHeaders());
        $this->assertEquals($headers,$new->getHeaders());
        $this->assertEquals('2.0',$message->getProtocolVersion());
        $this->assertEquals('2.0',$new->getProtocolVersion());
    }

    public function testMethod()
    {
        $message = new Request();
        $this->assertNull($message->getMethod());
        $new = $message->withMethod('get');
        $this->assertNull($message->getMethod());
        $this->assertEquals('GET',$new->getMethod());

        // clone
        $uri = new Uri('http://foouser@localhost:12345/boopath?bar=boo#foofragment');
        $body = new Stream();
        $headers = array('foo'=>array('bar'));
        $message = new Request($uri,'POST',$body,$headers,'2.0');
        $newUri = new Uri();
        $new = $message->withMethod('GET');
        $this->assertEquals('/boopath?bar=boo',$message->getRequestTarget());
        $this->assertEquals('/boopath?bar=boo',$new->getRequestTarget());
        $this->assertEquals(spl_object_hash($uri),spl_object_hash($message->getUri()));
        $this->assertEquals(spl_object_hash($uri),spl_object_hash($new->getUri()));
        $this->assertEquals('POST',$message->getMethod());
        $this->assertEquals('GET',$new->getMethod());
        $this->assertEquals(spl_object_hash($body),spl_object_hash($message->getBody()));
        $this->assertEquals(spl_object_hash($body),spl_object_hash($new->getBody()));
        $this->assertEquals($headers,$message->getHeaders());
        $this->assertEquals($headers,$new->getHeaders());
        $this->assertEquals('2.0',$message->getProtocolVersion());
        $this->assertEquals('2.0',$new->getProtocolVersion());
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Type of method must be string.: array
     */
    public function testInvalidMethodString()
    {
        $message = new Request();
        $this->assertNull($message->getMethod());
        $new = $message->withMethod(array());
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid method: FOO
     */
    public function testInvalidMethodInvalid()
    {
        $message = new Request();
        $this->assertNull($message->getMethod());
        $new = $message->withMethod('FOO');
    }

}
