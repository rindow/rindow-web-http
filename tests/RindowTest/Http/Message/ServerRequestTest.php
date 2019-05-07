<?php
namespace RindowTest\Web\Http\Message\ServerRequestTest;

use PHPUnit\Framework\TestCase;
use Rindow\Web\Http\Message\ServerRequest;
use Rindow\Web\Http\Message\Stream;
use Rindow\Web\Http\Message\Uri;
use Rindow\Web\Http\Message\UploadedFile;

class Test extends TestCase
{
    public function getServerParams()
    {
        return array(
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
    }

    public function testUri()
    {
        $message = new ServerRequest();
        $this->assertNull($message->getUri());

        $serverParams = $this->getServerParams();
        $message = new ServerRequest($serverParams);
        $uri = $message->getUri();
        $this->assertEquals('http',$uri->getScheme());
        $this->assertEquals('',$uri->getUserInfo());
        $this->assertEquals('localhost',$uri->getHost());
        $this->assertEquals(8080,$uri->getPort());
        $this->assertEquals('localhost:8080',$uri->getAuthority());
        $this->assertEquals('/test.php',$uri->getPath());
        $this->assertEquals('bar=boo',$uri->getQuery());
        $this->assertEquals('',$uri->getFragment());
        $this->assertEquals('http://localhost:8080/test.php?bar=boo',strval($uri));

        $uri = new Uri();
        $serverParams = $this->getServerParams();
        $message = new ServerRequest($serverParams,null,null,null,null,$uri);
        $this->assertEquals(spl_object_hash($uri),spl_object_hash($message->getUri()));

        $serverParams = array('REQUEST_URI'=>'/bar');
        $message = new ServerRequest($serverParams);
        $this->assertEquals('http://unknown-host/bar',strval($message->getUri()));

        $serverParams = $this->getServerParams();
        $serverParams['HTTPS'] = 'off';
        $message = new ServerRequest($serverParams);
        $uri = $message->getUri();
        $this->assertEquals('http://localhost:8080/test.php?bar=boo',strval($uri));
        $serverParams['HTTPS'] = 'on';
        $message = new ServerRequest($serverParams);
        $uri = $message->getUri();
        $this->assertEquals('https://localhost:8080/test.php?bar=boo',strval($uri));

    }

    public function testMethod()
    {
        $message = new ServerRequest();
        $this->assertNull($message->getMethod());

        $serverParams = $this->getServerParams();
        $message = new ServerRequest($serverParams);
        $this->assertEquals('POST',$message->getMethod());

        $message = new ServerRequest($serverParams,null,null,null,null,$uri=null,'GET');
        $this->assertEquals('GET',$message->getMethod());
    }

    public function testHeaders()
    {
        $message = new ServerRequest();
        $this->assertEquals(array(),$message->getHeaders());

        $serverParams = $this->getServerParams();
        $message = new ServerRequest($serverParams);
        $this->assertEquals(
            array(
                'host' => array('localhost:8080'),
                'connection' => array('keep-alive'),
                'content-length' => array(7),
                'cache-control' => array('max-age=0'),
                'upgrade-insecure-requests' => array(1),
                'user-agent' => array('Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36'),
                'content-type' => array('application/x-www-form-urlencoded'),
                'accept' => array('text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8'),
                'accept-encoding' => array('gzip, deflate, sdch'),
                'accept-language' => array('ja,en-US;q=0.8,en;q=0.6'),
            ),
            $message->getHeaders()
        );
        $this->assertEquals(array('localhost:8080'),$message->getHeader('Host'));
        // *** CAUTION ***
        // It can not extract multiline header.
        $this->assertEquals(array('gzip, deflate, sdch'),$message->getHeader('Accept-Encoding'));

        $message = new ServerRequest($serverParams,null,null,null,null,$uri=null,$method=null,$body=null,array());
        $this->assertEquals(array(),$message->getHeaders());
    }

    public function testProtocolVersion()
    {
        $message = new ServerRequest();
        $this->assertEquals('1.1',$message->getProtocolVersion());

        $serverParams = $this->getServerParams();
        $serverParams['SERVER_PROTOCOL'] = 'HTTP/1.0';
        $message = new ServerRequest($serverParams);
        $this->assertEquals('1.0',$message->getProtocolVersion());

        $message = new ServerRequest($serverParams,null,null,null,null,$uri=null,$method=null,$body=null,$headers=null,'2.0');
        $this->assertEquals('2.0',$message->getProtocolVersion());
    }

    public function testBody()
    {
        $message = new ServerRequest();
        $this->assertNull($message->getBody());

        $body = new Stream();
        $serverParams = $this->getServerParams();
        $message = new ServerRequest($serverParams,null,null,null,null,$uri=null,$method=null,$body);
        $this->assertEquals(spl_object_hash($body),spl_object_hash($message->getBody()));
    }

    public function testCookieParams()
    {
        $cookieParams = array(
            'PHPSESSID' => 'l5rq77612ubr7cgajmf62tlvv3',
        );

        $message = new ServerRequest();
        $this->assertEquals(array(),$message->getCookieParams());

        $serverParams = $this->getServerParams();
        $message = new ServerRequest($serverParams,$parsedBody=null,$uploadedParams=null,$cookieParams);
        $this->assertEquals($cookieParams,$message->getCookieParams());

        $new = $message->withCookieParams(array('FOO'=>'boo'));
        $this->assertEquals($cookieParams,$message->getCookieParams());
        $this->assertEquals(array('FOO'=>'boo'),$new->getCookieParams());
        $this->assertEquals($serverParams,$new->getServerParams());
    }

    public function testQueryParams()
    {
        $message = new ServerRequest();
        $this->assertEquals(array(),$message->getQueryParams());

        $serverParams = $this->getServerParams();
        $message = new ServerRequest($serverParams,$parsedBody=null,$uploadedParams=null,$cookieParams=null);
        $this->assertEquals(array('bar' => 'boo'),$message->getQueryParams());

        $new = $message->withQueryParams(array('FOO'=>'boo'));
        $this->assertEquals(array('bar' => 'boo'),$message->getQueryParams());
        $this->assertEquals(array('FOO'=>'boo'),$new->getQueryParams());
        $this->assertEquals($serverParams,$new->getServerParams());
    }

    public function testAttribute()
    {
        $attributes = array(
            'bar' => 'boo',
        );

        $message = new ServerRequest();
        $this->assertEquals(array(),$message->getAttributes());

        $serverParams = $this->getServerParams();
        $message = new ServerRequest($serverParams,$parsedBody=null,$uploadedParams=null,$cookieParams=null,$attributes);
        $this->assertEquals($attributes,$message->getAttributes());
        $this->assertEquals('boo',$message->getAttribute('bar'));
        $this->assertEquals(null,$message->getAttribute('none'));
        $this->assertEquals('foo',$message->getAttribute('none','foo'));

        $new = $message->withAttribute('bar','FOO');
        $this->assertEquals($attributes,$message->getAttributes());
        $this->assertEquals(array('bar'=>'FOO'),$new->getAttributes());
        $this->assertEquals($serverParams,$new->getServerParams());

        $new = $message->withoutAttribute('bar');
        $this->assertEquals($attributes,$message->getAttributes());
        $this->assertEquals(array(),$new->getAttributes());
        $this->assertEquals($serverParams,$new->getServerParams());
    }

    public function testParsedBody()
    {
        $parsedBody = array(
            'foo' => 'test',
        );

        $message = new ServerRequest();
        $this->assertEquals(array(),$message->getParsedBody());

        $serverParams = $this->getServerParams();
        $message = new ServerRequest($serverParams,$parsedBody,$uploadedParams=null,$cookieParams=null);
        $this->assertEquals($parsedBody,$message->getParsedBody());

        $new = $message->withParsedBody(array('FOO'=>'boo'));
        $this->assertEquals($parsedBody,$message->getParsedBody());
        $this->assertEquals(array('FOO'=>'boo'),$new->getParsedBody());
        $this->assertEquals($serverParams,$new->getServerParams());
    }

    public function testUploadedFiles()
    {
        $uploadedParams = array(
            'foo' => array(
                'name' => 'boo.gif',
                'type' => 'application/octet-stream',
                'tmp_name' => '/tmp/php4008.tmp',
                'error' => 0,
                'size' => 183,
            )
        );

        $message = new ServerRequest();
        $this->assertEquals(array(),$message->getUploadedFiles());

        $serverParams = $this->getServerParams();
        $message = new ServerRequest($serverParams,$parsedBody=null,$uploadedParams,$cookieParams=null);
        $uploadedFiles = $message->getUploadedFiles();
        $this->assertCount(1,$uploadedFiles);
        $this->assertInstanceof('Rindow\Web\Http\Message\UploadedFile',$uploadedFiles['foo']);
        $this->assertEquals('boo.gif',$uploadedFiles['foo']->getClientFilename());
        $this->assertEquals('application/octet-stream',$uploadedFiles['foo']->getClientMediaType());
        $this->assertNull($uploadedFiles['foo']->getError());
        $this->assertEquals(183,$uploadedFiles['foo']->getSize());

        $file = new UploadedFile(null,null,null,null,null);
        $new = $message->withUploadedFiles(array('foo'=>$file));
        $this->assertEquals($uploadedFiles,$message->getUploadedFiles());
        $this->assertEquals(array('foo'=>$file),$new->getUploadedFiles());
        $this->assertEquals($serverParams,$new->getServerParams());
    }

    public function testTreeUploadedFiles()
    {
        $uploadedParams = array(
            'foo' => array(
                'name' => array(
                    'details' => array(
                        'avatars' => array(
                            0 => 'avatars0.dmp',
                            1 => 'avatars1.json',
                        ),
                    ),
                    'baz' => array(
                        'boo' => array(
                            0 => 'boo.zip',
                        ),
                    ),
                ),
                'type' => array(
                    'details' => array(
                        'avatars' => array(
                            0 => 'application/octet-stream',
                            1 => 'application/json',
                        ),
                    ),
                    'baz' => array(
                        'boo' => array(
                            0 => 'application/octet-stream',
                        ),
                    ),
                ),
                'tmp_name' => array(
                    'details' => array(
                        'avatars' => array(
                            0 => '/tmp/avatars0.tmp',
                            1 => '/tmp/avatars1.tmp',
                        ),
                    ),
                    'baz' => array(
                        'boo' => array(
                            0 => '/tmp/boo.tmp',
                        ),
                    ),
                ),
                'error' => array(
                    'details' => array(
                        'avatars' => array(
                            0 => 0,
                            1 => 0,
                        ),
                    ),
                    'baz' => array(
                        'boo' => array(
                            0 => 0,
                        ),
                    ),
                ),
                'size' => array(
                    'details' => array(
                        'avatars' => array(
                            0 => 53813,
                            1 => 2442,
                        ),
                    ),
                    'baz' => array(
                        'boo' => array(
                            0 => 2400,
                        ),
                    ),
                ),
            ),
            'bar' => array(
                'name' => 'boo.gif',
                'type' => 'image/gif',
                'tmp_name' => '/tmp/boogif.tmp',
                'error' => 0,
                'size' => 183,
            ),
        );

        $serverParams = $this->getServerParams();
        $message = new ServerRequest($serverParams,$parsedBody=null,$uploadedParams,$cookieParams=null);
        $uploadedFiles = $message->getUploadedFiles();
        $this->assertCount(2,$uploadedFiles);

        $this->assertInstanceof('Rindow\Web\Http\Message\UploadedFile',$uploadedFiles['bar']);
        $this->assertEquals('boo.gif',$uploadedFiles['bar']->getClientFilename());
        $this->assertEquals('image/gif',$uploadedFiles['bar']->getClientMediaType());
        $this->assertNull($uploadedFiles['bar']->getError());
        $this->assertEquals(183,$uploadedFiles['bar']->getSize());

        $this->assertCount(2,$uploadedFiles['foo']);
        $this->assertCount(1,$uploadedFiles['foo']['details']);
        $this->assertCount(2,$uploadedFiles['foo']['details']['avatars']);
        $this->assertInstanceof('Rindow\Web\Http\Message\UploadedFile',$uploadedFiles['foo']['details']['avatars'][0]);
        $this->assertEquals('avatars0.dmp',$uploadedFiles['foo']['details']['avatars'][0]->getClientFilename());
        $this->assertEquals('application/octet-stream',$uploadedFiles['foo']['details']['avatars'][0]->getClientMediaType());
        $this->assertNull($uploadedFiles['foo']['details']['avatars'][0]->getError());
        $this->assertEquals(53813,$uploadedFiles['foo']['details']['avatars'][0]->getSize());
        $this->assertInstanceof('Rindow\Web\Http\Message\UploadedFile',$uploadedFiles['foo']['details']['avatars'][1]);
        $this->assertEquals('avatars1.json',$uploadedFiles['foo']['details']['avatars'][1]->getClientFilename());
        $this->assertEquals('application/json',$uploadedFiles['foo']['details']['avatars'][1]->getClientMediaType());
        $this->assertNull($uploadedFiles['foo']['details']['avatars'][1]->getError());
        $this->assertEquals(2442,$uploadedFiles['foo']['details']['avatars'][1]->getSize());

        $this->assertCount(1,$uploadedFiles['foo']['baz']);
        $this->assertCount(1,$uploadedFiles['foo']['baz']['boo']);
        $this->assertInstanceof('Rindow\Web\Http\Message\UploadedFile',$uploadedFiles['foo']['baz']['boo'][0]);
        $this->assertEquals('boo.zip',$uploadedFiles['foo']['baz']['boo'][0]->getClientFilename());
        $this->assertEquals('application/octet-stream',$uploadedFiles['foo']['baz']['boo'][0]->getClientMediaType());
        $this->assertNull($uploadedFiles['foo']['baz']['boo'][0]->getError());
        $this->assertEquals(2400,$uploadedFiles['foo']['baz']['boo'][0]->getSize());
    }

}
