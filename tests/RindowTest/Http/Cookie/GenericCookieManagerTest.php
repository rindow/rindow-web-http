<?php
namespace RindowTest\Web\Http\Cookie\GenericCookieManagerTest;

use PHPUnit\Framework\TestCase;
use Rindow\Web\Http\Cookie\GenericCookieManager;
use Rindow\Web\Http\Message\Response;

class Test extends TestCase
{
	public function testCreate()
	{
		$cookieManager = new GenericCookieManager();

		$cookie = $cookieManager->create('testname');
		$this->assertInstanceOf('Rindow\\Web\\Http\\Cookie\\GenericCookie',$cookie);
		$this->assertEquals('testname',$cookie->getName());
		$this->assertEquals('',$cookie->getValue());
		$this->assertEquals(0,$cookie->getExpires());
		$this->assertEquals('',$cookie->getPath());
		$this->assertEquals('',$cookie->getDomain());
		$this->assertFalse($cookie->getSecure());
		$this->assertFalse($cookie->getHttponly());

		$cookie = $cookieManager->create('testname','testvalue',10,'/testpath','domain.net',true,true);
		$this->assertEquals('testname',$cookie->getName());
		$this->assertEquals('testvalue',$cookie->getValue());
		$this->assertEquals(10,$cookie->getExpires());
		$this->assertEquals('/testpath',$cookie->getPath());
		$this->assertEquals('domain.net',$cookie->getDomain());
		$this->assertTrue($cookie->getSecure());
		$this->assertTrue($cookie->getHttponly());

		$cookie = $cookieManager->create('testname','',10,'','',true,false);
		$this->assertTrue($cookie->getSecure());
		$this->assertFalse($cookie->getHttponly());

		$cookie = $cookieManager->create('foo','bar');
		$this->assertInstanceOf('Rindow\\Web\\Http\\Cookie\\GenericCookie',$cookie);
		$this->assertEquals('foo',$cookie->getName());

		$this->assertCount(0,$cookieManager->getAll());
		$this->assertNull($cookieManager->get('foo'));

		$cookieManager->set($cookie);
		$this->assertCount(1,$cookieManager->getAll());
		$this->assertEquals('bar',$cookieManager->get('foo')->getValue());

		$cookieManager->set('testname');
		$cookie = $cookieManager->get('testname');
		$this->assertInstanceOf('Rindow\\Web\\Http\\Cookie\\GenericCookie',$cookie);
		$this->assertEquals('testname',$cookie->getName());
		$this->assertEquals('',$cookie->getValue());
		$this->assertEquals(0,$cookie->getExpires());
		$this->assertEquals('',$cookie->getPath());
		$this->assertEquals('',$cookie->getDomain());
		$this->assertFalse($cookie->getSecure());
		$this->assertFalse($cookie->getHttponly());

		$cookieManager->set('testname','testvalue',10,'/testpath','domain.net',true,true);
		$cookie = $cookieManager->get('testname');
		$this->assertEquals('testname',$cookie->getName());
		$this->assertEquals('testvalue',$cookie->getValue());
		$this->assertEquals(10,$cookie->getExpires());
		$this->assertEquals('/testpath',$cookie->getPath());
		$this->assertEquals('domain.net',$cookie->getDomain());
		$this->assertTrue($cookie->getSecure());
		$this->assertTrue($cookie->getHttponly());

		$cookieManager->set('testname','',10,'','',true,false);
		$cookie = $cookieManager->get('testname');
		$this->assertTrue($cookie->getSecure());
		$this->assertFalse($cookie->getHttponly());
	}

    /**
     * @expectedException        Rindow\Web\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage "name" must be CookieInterface or String
     */
	public function testSetInvalid()
	{
		$cookieManager = new GenericCookieManager();
		$cookieManager->set(false);
	}

	public function testSetDelete()
	{
		$cookieManager = new GenericCookieManager();
		$cookieManager->set('foo','bar');
		$cookieManager->set('foo2','bar2');
		$cookieManager->set('foo3','bar3');

		$this->assertNull($cookieManager->get('none'));
		$this->assertEquals('bar',$cookieManager->get('foo')->getValue());
		$this->assertEquals('bar2',$cookieManager->get('foo2')->getValue());
		$this->assertCount(3,$cookieManager->getAll());

		$cookieManager->delete('foo');
		$this->assertNull($cookieManager->get('foo'));

		$cookieManager->delete('foo2');
		$this->assertNull($cookieManager->get('foo2'));
		$this->assertCount(1,$cookieManager->getAll());

		$cookieManager->clear();
		$this->assertCount(0,$cookieManager->getAll());
	}

	public function testAddToResponseAndDelete()
	{
		$response = new Response();

		$this->assertEquals(array(),$response->getHeader('Set-Cookie'));

		$cookieManager = new GenericCookieManager();
		$cookieManager->set('foo','bar');
		$cookieManager->set('foo2','bar2');
		$cookieManager->set('foo3','bar3');

		$response = $cookieManager->addtoResponse($response);
		$this->assertEquals(array('foo=bar','foo2=bar2','foo3=bar3'),$response->getHeader('Set-Cookie'));

		$response = $cookieManager->deleteFromResponse($response);
		$this->assertEquals(array(),$response->getHeader('Set-Cookie'));
	}

	public function testMargeContext()
	{
		$cookieManager = new GenericCookieManager();
		$cookieManager->set('foo','bar');
		$cookieManager->set('foo2','bar2');

		$cookieManager2 = new GenericCookieManager();
		$cookieManager2->set('foo','bar+');
		$cookieManager2->set('foo3','bar3+');

		$cookieManager->margeContext($cookieManager2);
		$cookies = $cookieManager->getAll();
		$this->assertCount(3,$cookies);
		$this->assertEquals('bar+',$cookieManager->get('foo')->getValue());
		$this->assertEquals('bar2',$cookieManager->get('foo2')->getValue());
		$this->assertEquals('bar3+',$cookieManager->get('foo3')->getValue());
	}
}
