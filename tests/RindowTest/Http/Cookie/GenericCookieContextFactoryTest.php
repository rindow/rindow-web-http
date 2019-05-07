<?php
namespace RindowTest\Web\Http\Cookie\GenericCookieContextFactoryTest;

use PHPUnit\Framework\TestCase;
use Rindow\Web\Http\Cookie\GenericCookieContextFactory;

class Test extends TestCase
{
	public function test()
	{
		$factory = new GenericCookieContextFactory();
		$context = $factory->create();
		$this->assertInstanceOf('Rindow\Web\Http\Cookie\GenericCookieManager',$context);
	}
}
