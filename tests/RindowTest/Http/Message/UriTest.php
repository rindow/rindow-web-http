<?php
namespace RindowTest\Web\Http\Message\UriTest;

use PHPUnit\Framework\TestCase;
use Rindow\Web\Http\Message\Uri;

class Test extends TestCase
{
    public function testPartsNormal()
    {
        $uri = new Uri('http://foouser@localhost:12345/boopath?bar=boo#foofragment');
        $this->assertEquals('http',$uri->getScheme());
        $this->assertEquals('foouser',$uri->getUserInfo());
        $this->assertEquals('localhost',$uri->getHost());
        $this->assertEquals(12345,$uri->getPort());
        $this->assertEquals('foouser@localhost:12345',$uri->getAuthority());
        $this->assertEquals('/boopath',$uri->getPath());
        $this->assertEquals('bar=boo',$uri->getQuery());
        $this->assertEquals('foofragment',$uri->getFragment());
        $this->assertEquals('http://foouser@localhost:12345/boopath?bar=boo#foofragment',strval($uri));

        $uri = new Uri();
        $this->assertEquals('',$uri->getScheme());
        $this->assertEquals('',$uri->getUserInfo());
        $this->assertEquals('',$uri->getHost());
        $this->assertEquals(null,$uri->getPort());
        $this->assertEquals('',$uri->getAuthority());
        $this->assertEquals('',$uri->getPath());
        $this->assertEquals('',$uri->getQuery());
        $this->assertEquals('',$uri->getFragment());

        $uri = new Uri('http://foouser@localhost:12345/boopath?bar=boo#foofragment');

        $new = $uri->withScheme('https');
        $this->assertEquals('http',$uri->getScheme());
        $this->assertEquals('https',$new->getScheme());
        $this->assertEquals('foouser@localhost:12345',$new->getAuthority());

        $new = $uri->withUserInfo('bar','boopass');
        $this->assertEquals('foouser',$uri->getUserInfo());
        $this->assertEquals('bar:boopass',$new->getUserInfo());
        $this->assertEquals('foouser@localhost:12345',$uri->getAuthority());
        $this->assertEquals('bar@localhost:12345',$new->getAuthority());

        $new = $uri->withHost('foofoo');
        $this->assertEquals('localhost',$uri->getHost());
        $this->assertEquals('foofoo',$new->getHost());
        $this->assertEquals('foouser@foofoo:12345',$new->getAuthority());

        $new = $uri->withPort(1000);
        $this->assertEquals(12345,$uri->getPort());
        $this->assertEquals(1000,$new->getPort());
        $this->assertEquals('foouser@localhost:1000',$new->getAuthority());

        $new = $uri->withPath('/foopath');
        $this->assertEquals('/boopath',$uri->getPath());
        $this->assertEquals('/foopath',$new->getPath());
        $this->assertEquals('foouser@localhost:12345',$new->getAuthority());

        $new = $uri->withQuery('foo=far');
        $this->assertEquals('bar=boo',$uri->getQuery());
        $this->assertEquals('foo=far',$new->getQuery());
        $this->assertEquals('foouser@localhost:12345',$new->getAuthority());

        $new = $uri->withFragment('boofragment');
        $this->assertEquals('foofragment',$uri->getFragment());
        $this->assertEquals('boofragment',$new->getFragment());
        $this->assertEquals('foouser@localhost:12345',$new->getAuthority());
    }
}