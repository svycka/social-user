<?php

namespace Svycka\SocialUserTest\Service\Factory;

use Interop\Container\ContainerInterface;
use Svycka\SocialUser\OAuth2\GrantType;
use Svycka\SocialUser\Service\SocialUserService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class GoogleFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreate()
    {
        $services = $this->prophesize(ServiceLocatorInterface::class);
        $services->willImplement(ContainerInterface::class);
        $services->get('Config')->willReturn([
            'svycka_social_user' => [
                'grant_type_options' => [
                    GrantType\Google::class => [
                        'audience' => 'google_app_id',
                    ]
                ]
            ]
        ]);

        $service = $this->prophesize(SocialUserService::class);
        $services->get(SocialUserService::class)->willReturn($service->reveal());

        $factory = new GrantType\Factory\GoogleFactory();
        $service = $factory->createService($services->reveal());

        $this->assertInstanceOf(GrantType\Google::class, $service);
    }

    public function testThrowExceptionIfFacebookApiOptionsNotProvided()
    {
        $services = $this->prophesize(ServiceLocatorInterface::class);
        $services->willImplement(ContainerInterface::class);
        $services->get('Config')->willReturn([
            'svycka_social_user' => [
                'grant_type_options' => []
            ]
        ]);

        $this->setExpectedException(ServiceNotCreatedException::class, sprintf(
            '"%s" options not set',
            GrantType\Google::class
        ));

        $factory = new GrantType\Factory\GoogleFactory();
        $factory->createService($services->reveal());
    }
}
