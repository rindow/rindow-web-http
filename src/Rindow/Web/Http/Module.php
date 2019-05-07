<?php
namespace Rindow\Web\Http;

class Module
{
    public function getConfig()
    {
        return array(
            'container' => array(
                'aliases' => array(
                    'Rindow\\Web\\Mvc\\DefaultServerRequest' => 'Rindow\\Web\\Http\\Message\\DefaultServerRequest',
                    'Rindow\\Web\\Mvc\\DefaultResponse'      => 'Rindow\\Web\\Http\\Message\\DefaultResponse',
                    'Rindow\\Web\\Mvc\\DefaultCookieContextFactory' => 'Rindow\\Web\\Http\\Cookie\\DefaultCookieContextFactory',
                ),
                'components' => array(
                    'Rindow\\Web\\Http\\Message\\DefaultResponse' => array(
                        'class' => 'Rindow\\Web\\Http\\Message\\Response',
                        'factory' => 'Rindow\\Web\\Http\\Message\\ResponseFactory::create',
                    ),
                    'Rindow\\Web\\Http\\Message\\DefaultServerRequest' => array(
                        'class' => 'Rindow\\Web\\Http\\Message\\ServerRequest',
                        'factory' => 'Rindow\\Web\\Http\\Message\\ServerRequestFactory::factory',
                        'factory_args' => array(
                            'testEnvironment' => 'Rindow\\Web\\Http\\Message\\TestModeEnvironment',
                            'testmode' => getenv('UNITTEST'),
                        ),
                    ),
                    'Rindow\\Web\\Http\\Message\\TestModeServerRequest' => array(
                        'class' => 'Rindow\\Web\\Http\\Message\\ServerRequest',
                        'factory' => 'Rindow\\Web\\Http\\Message\\ServerRequestFactory::factoryTestEnvironment',
                        'factory_args' => array(
                            'testEnvironment' => 'Rindow\\Web\\Http\\Message\\TestModeEnvironment',
                        ),
                    ),
                    'Rindow\\Web\\Http\\Message\\TestModeEnvironment' => array(
                    ),
                    'Rindow\\Web\\Http\\Cookie\\DefaultCookieContext' => array(
                        'class' => 'Rindow\\Web\\Http\\Cookie\\GenericCookieManager',
                    ),
                    'Rindow\\Web\\Http\\Cookie\\DefaultCookieContextFactory' => array(
                        'class' => 'Rindow\\Web\\Http\\Cookie\\GenericCookieContextFactory',
                    ),
                ),
            ),
        );
    }
}
