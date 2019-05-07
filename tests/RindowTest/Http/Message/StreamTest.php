<?php
namespace RindowTest\Web\Http\Message\StreamTest;

use PHPUnit\Framework\TestCase;
use Rindow\Web\Http\Message\Stream;

class Test extends TestCase
{
    public function testMetadata()
    {
        $resource = fopen('php://memory', 'rw');
        $stream = new Stream($resource);
        $this->assertTrue($stream->isSeekable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isReadable());
        $metadata = $stream->getMetadata();
        $this->assertEquals('MEMORY',$metadata['stream_type']);
        $this->assertEquals('php://memory',$stream->getMetadata('uri'));
        $this->assertNull($stream->getMetadata('none'));

        $resource2 = $stream->detach();

        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isReadable());
        $this->assertNull($stream->getMetadata());

        $resource = fopen('php://memory', 'r');
        $stream = new Stream($resource);
        $this->assertFalse($stream->isWritable());
        $this->assertTrue($stream->isReadable());
        $stream->detach();

        $resource = fopen('php://memory', 'w');
        $stream = new Stream($resource);
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isReadable()); // **CAUTION** Readable!! the mode is "w+b".
        $stream->detach();
    }

    public function testReadWriteNormal()
    {
        $resource = fopen('php://memory', 'rw');
        $stream = new Stream($resource);

        $this->assertEquals(8,$stream->write('abcdefgh'));
        $this->assertEquals(8,$stream->tell());
        $this->assertEquals(8,$stream->getSize());

        $stream->rewind();
        $this->assertFalse($stream->eof());
        $this->assertEquals(0,$stream->tell());
        $this->assertEquals(8,$stream->getSize());

        $this->assertEquals('abcd',$stream->read(4));
        $this->assertFalse($stream->eof());
        $this->assertEquals(4,$stream->tell());
        $this->assertEquals(8,$stream->getSize());

        $this->assertEquals('efgh',$stream->read(10));
        //$this->assertFalse($stream->eof()); // **CAUTION** eof(PHP5.6)==false eof(PHP5.3)==true
        $this->assertEquals(8,$stream->tell());
        $this->assertEquals(8,$stream->getSize());

        $this->assertEquals('',$stream->read(10));
        $this->assertTrue($stream->eof());
        $this->assertEquals(8,$stream->tell());
        $this->assertEquals(8,$stream->getSize());
    }

    /**
     * @expectedException        Rindow\Web\Http\Exception\RuntimeException
     * @expectedExceptionMessage Stream is not writable
     */
    public function testWritableException()
    {
        $resource = fopen('php://memory', 'r');
        $stream = new Stream($resource);
        $this->assertFalse($stream->isWritable());
        $stream->write('abcdefgh');
    }
}