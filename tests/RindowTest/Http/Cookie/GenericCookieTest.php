<?php
namespace RindowTest\Web\Http\Cookie\GenericCookieTest;

use PHPUnit\Framework\TestCase;
use Rindow\Web\Http\Message\Response;
use Rindow\Web\Http\Cookie\GenericCookie;

class Test extends TestCase
{
	public function testCookie()
	{
		$cookie = new GenericCookie('testname');
		$this->assertEquals('testname',$cookie->getName());
		$this->assertEquals('',$cookie->getValue());
		$this->assertEquals(0,$cookie->getExpires());
		$this->assertEquals('',$cookie->getPath());
		$this->assertEquals('',$cookie->getDomain());
		$this->assertFalse($cookie->getSecure());
		$this->assertFalse($cookie->getHttponly());

		$cookie->setValue('testvalue');
		$cookie->setExpires(10);
		$cookie->setPath('/testpath');
		$cookie->setDomain('domain.net');
		$cookie->setSecure(true);
		$cookie->setHttponly(true);
		$this->assertEquals('testname',$cookie->getName());
		$this->assertEquals('testvalue',$cookie->getValue());
		$this->assertEquals(10,$cookie->getExpires());
		$this->assertEquals('/testpath',$cookie->getPath());
		$this->assertEquals('domain.net',$cookie->getDomain());
		$this->assertTrue($cookie->getSecure());
		$this->assertTrue($cookie->getHttponly());

		$cookie = new GenericCookie('testname','testvalue',10,'/testpath','domain.net',true,true);
		$this->assertEquals('testname',$cookie->getName());
		$this->assertEquals('testvalue',$cookie->getValue());
		$this->assertEquals(10,$cookie->getExpires());
		$this->assertEquals('/testpath',$cookie->getPath());
		$this->assertEquals('domain.net',$cookie->getDomain());
		$this->assertTrue($cookie->getSecure());
		$this->assertTrue($cookie->getHttponly());

		$cookie->setSecure(false);
		$this->assertFalse($cookie->getSecure());
		$this->assertTrue($cookie->getHttponly());

		$cookie->setHttponly(false);
		$this->assertFalse($cookie->getSecure());
		$this->assertFalse($cookie->getHttponly());

		$cookie = new GenericCookie('testname','testvalue',10,'/testpath','domain.net',false,true);
		$this->assertFalse($cookie->getSecure());
		$this->assertTrue($cookie->getHttponly());
	}
/*
	public function testResponseWithNewCookieAndReplaceAndAdd()
	{
		$response = new Response();
		$cookie = new GenericCookie('testname','testvalue');

		$responseNew = $response->withCookie($cookie);
		$this->assertNotEquals(spl_object_hash($response),spl_object_hash($responseNew));
		$this->assertCount(0,$response->getCookies());

		$cookies = $responseNew->getCookies();
		$this->assertCount(1,$cookies);
		$results = array();
		foreach ($cookies as $cookie) {
			$results[$cookie->getName()] = $cookie->getValue();
		}
		$this->assertEquals(array('testname'=>'testvalue'),$results);

		$cookie = new GenericCookie('testname','replacedvalue');
		$responseReplaced = $responseNew->withCookie($cookie);
		$this->assertNotEquals(spl_object_hash($responseNew),spl_object_hash($responseReplaced));
		$this->assertCount(1,$responseNew->getCookies());

		$cookies = $responseReplaced->getCookies();
		$this->assertCount(1,$cookies);
		$results = array();
		foreach ($cookies as $cookie) {
			$results[$cookie->getName()] = $cookie->getValue();
		}
		$this->assertEquals(array('testname'=>'replacedvalue'),$results);

		$cookie = new GenericCookie('newname','newvalue');
		$responseAdded = $responseReplaced->withCookie($cookie);
		$this->assertNotEquals(spl_object_hash($responseNew),spl_object_hash($responseReplaced));
		$this->assertCount(1,$responseReplaced->getCookies());

		$cookies = $responseAdded->getCookies();
		$this->assertCount(2,$cookies);
		$results = array();
		foreach ($cookies as $cookie) {
			$results[$cookie->getName()] = $cookie->getValue();
		}
		$this->assertEquals(array('testname'=>'replacedvalue','newname'=>'newvalue'),$results);
	}
*/
	public function testToString()
	{
		$time = \DateTime::createFromFormat('j-M-Y G:i:s T','17-Jan-2017 03:14:07 GMT');
		$cookie = new GenericCookie('fooName','fooValue');
		if(version_compare(PHP_VERSION, '5.5.0')>=0) {
			$expires = 'Tuesday, 17-Jan-2017 03:14:07 GMT';
			$deleted = 'Thursday, 01-Jan-1970 00:00:00 GMT';
		} else if(version_compare(PHP_VERSION, '5.4.0')>=0) {
			$expires = 'Tuesday, 17-Jan-2017 03:14:07 UTC';
			$deleted = 'Thursday, 01-Jan-1970 00:00:00 UTC';
		} else {
			$expires = 'Tuesday, 17-Jan-17 03:14:07 UTC';
			$deleted = 'Thursday, 01-Jan-70 00:00:00 UTC';
		}

		$this->assertEquals('fooName=fooValue',$cookie->toString());

		$cookie->setExpires($time->getTimestamp());
		$this->assertEquals('fooName=fooValue; Expires='.$expires.'',$cookie->toString());

		$cookie->setPath('/foo');
		$this->assertEquals('fooName=fooValue; Expires='.$expires.'; Path=/foo',$cookie->toString());

		$cookie->setDomain('foo.com');
		$this->assertEquals('fooName=fooValue; Expires='.$expires.'; Path=/foo; Domain=foo.com',$cookie->toString());

		$cookie->setSecure(true);
		$this->assertEquals('fooName=fooValue; Expires='.$expires.'; Path=/foo; Domain=foo.com; Secure',$cookie->toString());

		$cookie->setHttponly(true);
		$this->assertEquals('fooName=fooValue; Expires='.$expires.'; Path=/foo; Domain=foo.com; Secure; HttpOnly',$cookie->toString());

		/// 
		$cookie->setExpires(0);
		$this->assertEquals('fooName=fooValue; Path=/foo; Domain=foo.com; Secure; HttpOnly',$cookie->toString());

		$cookie->setPath('');
		$this->assertEquals('fooName=fooValue; Domain=foo.com; Secure; HttpOnly',$cookie->toString());

		$cookie->setDomain('');
		$this->assertEquals('fooName=fooValue; Secure; HttpOnly',$cookie->toString());

		$cookie->setSecure(false);
		$this->assertEquals('fooName=fooValue; HttpOnly',$cookie->toString());

		$cookie->setHttponly(false);
		$this->assertEquals('fooName=fooValue',$cookie->toString());

		$cookie->setValue('');
		$this->assertEquals('fooName=deleted; Expires='.$deleted,$cookie->toString());
	}

	public function testAddToResponseAndDelete()
	{
		$response = new Response();

		$this->assertEquals(array(),$response->getHeader('Set-Cookie'));

		$cookie = new GenericCookie('foo','bar');
		$response = $cookie->addtoResponse($response);
		$this->assertEquals(array('foo=bar'),$response->getHeader('Set-Cookie'));

		$cookie = new GenericCookie('foo','boo');
		$response = $cookie->addtoResponse($response);
		$this->assertEquals(array('foo=boo'),$response->getHeader('Set-Cookie'));

		$cookie = new GenericCookie('foo2','bar2');
		$response = $cookie->addtoResponse($response);
		$this->assertEquals(array('foo=boo','foo2=bar2'),$response->getHeader('Set-Cookie'));

		$cookie = new GenericCookie('foo','B++');
		$response = $cookie->addtoResponse($response);
		$this->assertEquals(array('foo=B++','foo2=bar2'),$response->getHeader('Set-Cookie'));

		$cookie = new GenericCookie('foo2','B2++');
		$response = $cookie->addtoResponse($response);
		$this->assertEquals(array('foo=B++','foo2=B2++'),$response->getHeader('Set-Cookie'));

		$cookie = new GenericCookie('foo2','B2++');
		$response = $cookie->deleteFromResponse($response);
		$this->assertEquals(array('foo=B++'),$response->getHeader('Set-Cookie'));

		$cookie = new GenericCookie('foo','B++');
		$response = $cookie->deleteFromResponse($response);
		$this->assertEquals(array(),$response->getHeader('Set-Cookie'));
	}
}
