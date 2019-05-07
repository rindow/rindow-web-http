<?php
namespace RindowTest\Web\Http\Message\MessageTest;

use PHPUnit\Framework\TestCase;
use Rindow\Web\Http\Message\Message;
use Rindow\Web\Http\Message\Stream;

class Test extends TestCase
{
    public function testDefault()
    {
        $message = new Message();
        $this->assertEquals('1.1',$message->getProtocolVersion());
        $this->assertEquals(array(),$message->getHeaders());
        $this->assertNull($message->getBody());
    }

    public function testProtecolVersion()
    {
        $message = new Message(null,null,'2.0');
        $this->assertEquals('2.0',$message->getProtocolVersion());
        $new10 = $message->withProtocolVersion('1.0');
        $this->assertEquals('2.0',$message->getProtocolVersion());
        $this->assertEquals('1.0',$new10->getProtocolVersion());
        $new11 = $new10->withProtocolVersion('1.1');
        $this->assertEquals('1.0',$new10->getProtocolVersion());
        $this->assertEquals('1.1',$new11->getProtocolVersion());

        // clone
        $body = new Stream();
        $headers = array(array('foo'=>array('bar')));
        $message = new Message($body,$headers,'2.0');
        $new = $message->withProtocolVersion('1.1');
        $this->assertEquals(spl_object_hash($body),spl_object_hash($new->getBody()));
        $this->assertEquals($headers,$new->getHeaders());
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid protocol version: 0.0
     */
    public function testInvalidProtecolVersionString()
    {
        $message = new Message();
        $new10 = $message->withProtocolVersion('0.0');
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid protocol version: stdClass
     */
    public function testInvalidProtecolVersionObject()
    {
        $message = new Message();
        $obj = new \stdClass();
        $new10 = $message->withProtocolVersion($obj);
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid protocol version: NULL
     */
    public function testInvalidProtecolVersionNull()
    {
        $message = new Message();
        $new10 = $message->withProtocolVersion(null);
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid protocol version: array
     */
    public function testInvalidProtecolVersionArray()
    {
        $message = new Message();
        $new10 = $message->withProtocolVersion(array());
    }

    public function testHeader()
    {
        $message = new Message(null,array('fooheader'=>array('barvalue','boovalue')));
        $this->assertEquals(array('fooheader'=>array('barvalue','boovalue')),$message->getHeaders());

        $this->assertTrue($message->hasHeader('fooheader'));
        $this->assertFalse($message->hasHeader('noneheader'));

        $this->assertEquals(array('barvalue','boovalue'),$message->getHeader('fooheader'));
        $this->assertEquals(array(),$message->getHeader('noneheader'));

        $this->assertEquals('barvalue,boovalue',$message->getHeaderLine('fooheader'));
        $this->assertEquals('',$message->getHeaderLine('noneheader'));

        $this->assertEquals(array('fooheader'=>array('newvalue')),$message->withHeader('fooheader','newvalue')->getHeaders());
        $this->assertEquals(array('fooheader'=>array('newvalue')),$message->withHeader('fooheader',array('newvalue'))->getHeaders());
        $this->assertEquals(array('fooheader'=>array('barvalue','boovalue')),$message->getHeaders());
        $this->assertEquals(array('fooheader'=>array('barvalue','boovalue'),'newheader'=>array('newvalue')),$message->withHeader('newheader',array('newvalue'))->getHeaders());
        $this->assertEquals(array('fooheader'=>array('barvalue','boovalue')),$message->getHeaders());

        $this->assertEquals(array('fooheader'=>array('barvalue','boovalue','newvalue')),$message->withAddedHeader('fooheader','newvalue')->getHeaders());
        $this->assertEquals(array('fooheader'=>array('barvalue','boovalue','newvalue')),$message->withAddedHeader('fooheader',array('newvalue'))->getHeaders());
        $this->assertEquals(array('fooheader'=>array('barvalue','boovalue')),$message->getHeaders());
        $this->assertEquals(array('fooheader'=>array('barvalue','boovalue'),'newheader'=>array('newvalue')),$message->withAddedHeader('newheader',array('newvalue'))->getHeaders());
        $this->assertEquals(array('fooheader'=>array('barvalue','boovalue')),$message->getHeaders());

        $this->assertEquals(array(),$message->withoutHeader('fooheader')->getHeaders());
        $this->assertEquals(array('fooheader'=>array('barvalue','boovalue')),$message->getHeaders());
        $this->assertEquals(array('fooheader'=>array('barvalue','boovalue')),$message->withoutHeader('newheader')->getHeaders());
        $this->assertEquals(array('fooheader'=>array('barvalue','boovalue')),$message->getHeaders());

        // Case-insensitive
        $this->assertTrue($message->hasHeader('fooHEADER'));
        $this->assertEquals(array('barvalue','boovalue'),$message->getHeader('fooHEADER'));
        $this->assertEquals('barvalue,boovalue',$message->getHeaderLine('fooHEADER'));
        $this->assertEquals(array('fooHEADER'=>array('newVALUE')),$message->withHeader('fooHEADER',array('newVALUE'))->getHeaders());
        $this->assertEquals(array('fooheader'=>array('barvalue','boovalue','newVALUE')),$message->withAddedHeader('fooHEADER',array('newVALUE'))->getHeaders());
        $this->assertEquals(array(),$message->withoutHeader('fooHEADER')->getHeaders());

        // clone
        $body = new Stream();
        $headers = array(array('foo'=>array('bar')));
        $message = new Message($body,$headers,'2.0');
        $new = $message->withHeader('fooheader','newvalue');
        $this->assertEquals(spl_object_hash($body),spl_object_hash($new->getBody()));
        $this->assertEquals('2.0',$new->getProtocolVersion());
        $new = $message->withAddedHeader('fooheader','newvalue');
        $this->assertEquals(spl_object_hash($body),spl_object_hash($new->getBody()));
        $this->assertEquals('2.0',$new->getProtocolVersion());
        $new = $message->withoutHeader('foo');
        $this->assertEquals(spl_object_hash($body),spl_object_hash($new->getBody()));
        $this->assertEquals('2.0',$new->getProtocolVersion());
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Type of Header name must be string.: stdClass
     */
    public function testInvalidHeaderObject()
    {
        $obj = new \stdClass();
        $message = new Message();
        $this->assertTrue($message->hasHeader($obj));
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Type of Header name must be string.: NULL
     */
    public function testInvalidHeaderNull()
    {
        $message = new Message();
        $this->assertTrue($message->hasHeader(null));
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Type of Header name must be string.: NULL
     */
    public function testInvalidHeaderInWithHeader()
    {
        $message = new Message();
        $this->assertTrue($message->withHeader(null,'foo'));
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Type of Header name must be string.: NULL
     */
    public function testInvalidHeaderInWithAddedHeader()
    {
        $message = new Message();
        $this->assertTrue($message->withAddedHeader(null,'foo'));
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Type of Header name must be string.: NULL
     */
    public function testInvalidHeaderInWithoutHeader()
    {
        $message = new Message();
        $this->assertTrue($message->withoutHeader(null));
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Type of value must be string or array of string.: stdClass
     */
    public function testInvalidValueObject()
    {
        $obj = new \stdClass();
        $message = new Message();
        $this->assertTrue($message->withHeader('foo',$obj));
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Type of value must be string or array of string.: NULL
     */
    public function testInvalidValueNull()
    {
        $message = new Message();
        $this->assertTrue($message->withHeader('foo',null));
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Type of value must be string or array of string.: stdClass
     */
    public function testInvalidValueArrayObject()
    {
        $obj = new \stdClass();
        $message = new Message();
        $this->assertTrue($message->withHeader('foo',array($obj)));
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Type of value must be string or array of string.: NULL
     */
    public function testInvalidValueArrayNull()
    {
        $message = new Message();
        $this->assertTrue($message->withHeader('foo',array(null)));
    }

    public function testBody()
    {
        $resource = fopen('php://memory', 'r');
        $body = new Stream($resource);
        $message = new Message($body);
        $this->assertInstanceof('Rindow\Web\Http\Message\Stream',$message->getBody());
        $this->assertFalse($message->getBody()->isWritable());

        $resource = fopen('php://memory', 'rw');
        $body = new Stream($resource);
        $new = $message->withBody($body);
        $this->assertInstanceof('Rindow\Web\Http\Message\Stream',$new->getBody());
        $this->assertTrue($new->getBody()->isWritable());
        $this->assertFalse($message->getBody()->isWritable());

        // clone
        $body = new Stream();
        $headers = array(array('foo'=>array('bar')));
        $message = new Message($body,$headers,'2.0');
        $new = $message->withBody(new Stream());
        $this->assertEquals($headers,$new->getHeaders());
        $this->assertEquals('2.0',$new->getProtocolVersion());
    }
}
