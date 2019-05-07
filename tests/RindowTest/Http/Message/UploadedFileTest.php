<?php
namespace RindowTest\Web\Http\Message\UploadedFileTest;

use PHPUnit\Framework\TestCase;
use Rindow\Web\Http\Message\UploadedFile;

class Test extends TestCase
{
    public function testNormalRead()
    {
        $clientFilename = 'testfile.txt';
        $clientMediaType = 'application/octet-stream';
        $realPath = __DIR__.'/testfile.txt';
        $error = 0;
        $size = 14;
        $uploadedFile = new UploadedFile(
            $clientFilename,$clientMediaType,$realPath,$error,$size);
        $this->assertEquals('testfile.txt',$uploadedFile->getClientFilename());
        $this->assertEquals('application/octet-stream',$uploadedFile->getClientMediaType());
        $this->assertEquals(null,$uploadedFile->getError());
        $this->assertEquals(14,$uploadedFile->getSize());

        $stream = $uploadedFile->getStream();
        $text = $stream->getContents();
        $stream->close();
        $this->assertEquals('TEST TEST TEST',$text);
    }
}