<?php
namespace RindowTest\Web\Http\Message\ResponseTest;

use PHPUnit\Framework\TestCase;
use Rindow\Web\Http\Message\Stream;
use Rindow\Web\Http\Message\Response;

class Test extends TestCase
{
    public function testStatusCode()
    {
        $message = new Response();
        $this->assertEquals(200,$message->getStatusCode());
        $new = $message->withStatus(301);
        $this->assertEquals(200,$message->getStatusCode());
        $this->assertEquals(301,$new->getStatusCode());

        // clone
        $body = new Stream();
        $headers = array('foo'=>array('bar'));
        $message = new Response(400,'Foo',$body,$headers,'2.0');
        $new = $message->withStatus(301);
        $this->assertEquals(400,$message->getStatusCode());
        $this->assertEquals(301,$new->getStatusCode());
        $this->assertEquals('Foo',$message->getReasonPhrase());
        $this->assertEquals('Moved Permanently',$new->getReasonPhrase());
        $this->assertEquals(spl_object_hash($body),spl_object_hash($message->getBody()));
        $this->assertEquals(spl_object_hash($body),spl_object_hash($new->getBody()));
        $this->assertEquals($headers,$message->getHeaders());
        $this->assertEquals($headers,$new->getHeaders());
        $this->assertEquals('2.0',$message->getProtocolVersion());
        $this->assertEquals('2.0',$new->getProtocolVersion());
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid status code: array
     */
    public function testInvalidStatusCodeArray()
    {
        $message = new Response();
        $new = $message->withStatus(array());
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid status code: 1000
     */
    public function testInvalidStatusCodeReason()
    {
        $message = new Response();
        $new = $message->withStatus(1000);
    }

    public function testReasonPhrase()
    {
        $message = new Response();
        $this->assertEquals('OK',$message->getReasonPhrase());
        $new = $message->withStatus(301);
        $this->assertEquals('OK',$message->getReasonPhrase());
        $this->assertEquals('Moved Permanently',$new->getReasonPhrase());
        $new = $message->withStatus(301,'Foo Reason');
        $this->assertEquals('OK',$message->getReasonPhrase());
        $this->assertEquals('Foo Reason',$new->getReasonPhrase());

        // clone
        $body = new Stream();
        $headers = array('foo'=>array('bar'));
        $message = new Response(400,'Foo',$body,$headers,'2.0');
        $new = $message->withStatus(301,'Foo Reason');
        $this->assertEquals(400,$message->getStatusCode());
        $this->assertEquals(301,$new->getStatusCode());
        $this->assertEquals('Foo',$message->getReasonPhrase());
        $this->assertEquals('Foo Reason',$new->getReasonPhrase());
        $this->assertEquals(spl_object_hash($body),spl_object_hash($message->getBody()));
        $this->assertEquals(spl_object_hash($body),spl_object_hash($new->getBody()));
        $this->assertEquals($headers,$message->getHeaders());
        $this->assertEquals($headers,$new->getHeaders());
        $this->assertEquals('2.0',$message->getProtocolVersion());
        $this->assertEquals('2.0',$new->getProtocolVersion());
    }
}
