<?php

namespace Svycka\SocialUserTest\Service\Factory;

use Interop\Container\ContainerInterface;
use Svycka\SocialUser\OAuth2\GrantType;
use Svycka\SocialUser\Service\SocialUserService;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class FacebookFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreate()
    {
        $services = $this->prophesize(ServiceLocatorInterface::class);
        $services->willImplement(ContainerInterface::class);
        $services->get('config')->willReturn([
            'svycka_social_user' => [
                'grant_type_options' => [
                    GrantType\Facebook::class => [
                        'app_id' => 'FB_APP_ID',
                        'app_secret' => 'FB_APP_SECRET',
                    ]
                ]
            ]
        ]);

        $service = $this->prophesize(SocialUserService::class);
        $services->get(SocialUserService::class)->willReturn($service->reveal());

        $factory = new GrantType\Factory\FacebookFactory();
        $service = $factory->createService($services->reveal());

        $this->assertInstanceOf(GrantType\Facebook::class, $service);
    }

    public function testThrowExceptionIfFacebookApiOptionsNotProvided()
    {
        $services = $this->prophesize(ServiceLocatorInterface::class);
        $services->willImplement(ContainerInterface::class);
        $services->get('config')->willReturn([
            'svycka_social_user' => [
                'grant_type_options' => []
            ]
        ]);

        $this->setExpectedException(ServiceNotCreatedException::class, 'Facebook API options are not set');

        $factory = new GrantType\Factory\FacebookFactory();
        $factory->createService($services->reveal());
    }
}
