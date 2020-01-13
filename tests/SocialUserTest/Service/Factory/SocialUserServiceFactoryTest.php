<?php

namespace Svycka\SocialUserTest\Service\Factory;

use Interop\Container\ContainerInterface;
use Svycka\SocialUser\LocalUserProviderInterface;
use Svycka\SocialUser\Service\Factory\SocialUserServiceFactory;
use Svycka\SocialUser\Service\SocialUserService;
use Svycka\SocialUser\Storage\SocialUserStorageInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class SocialUserServiceFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCanCreate()
    {
        $services = $this->prophesize(ServiceLocatorInterface::class);
        $services->willImplement(ContainerInterface::class);
        $services->get('config')->willReturn([
            'svycka_social_user' => [
                'local_user_provider' => LocalUserProviderInterface::class,
                'social_user_storage' => SocialUserStorageInterface::class,
            ]
        ]);

        $provider = $this->prophesize(LocalUserProviderInterface::class);
        $services->get(LocalUserProviderInterface::class)->willReturn($provider->reveal());

        $storage = $this->prophesize(SocialUserStorageInterface::class);
        $services->get(SocialUserStorageInterface::class)->willReturn($storage->reveal());

        $factory = new SocialUserServiceFactory();
        $service = $factory->createService($services->reveal());
        $this->assertInstanceOf(SocialUserService::class, $service);
    }

    public function testThrowExceptionIfInvalidLocalUserProvider()
    {
        $services = $this->prophesize(ServiceLocatorInterface::class);
        $services->willImplement(ContainerInterface::class);
        $services->get('config')->willReturn([
            'svycka_social_user' => [
                'local_user_provider' => 'invalid',
                'social_user_storage' => SocialUserStorageInterface::class,
            ]
        ]);

        $services->get('invalid')->willReturn(new \stdClass());

        $storage = $this->prophesize(SocialUserStorageInterface::class);
        $services->get(SocialUserStorageInterface::class)->willReturn($storage->reveal());

        $factory = new SocialUserServiceFactory();
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(sprintf(
            'Invalid "local_user_provider" specified expected class name with implements "%s"',
            LocalUserProviderInterface::class
        ));
        $factory->createService($services->reveal());
    }

    public function testThrowExceptionIfInvalidStorage()
    {
        $services = $this->prophesize(ServiceLocatorInterface::class);
        $services->willImplement(ContainerInterface::class);
        $services->get('config')->willReturn([
            'svycka_social_user' => [
                'local_user_provider' => LocalUserProviderInterface::class,
                'social_user_storage' => 'invalid',
            ]
        ]);

        $provider = $this->prophesize(LocalUserProviderInterface::class);
        $services->get(LocalUserProviderInterface::class)->willReturn($provider->reveal());

        $services->get('invalid')->willReturn(new \stdClass());

        $factory = new SocialUserServiceFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(sprintf(
            'Invalid "social_user_storage" specified expected class name with implements "%s"',
            SocialUserStorageInterface::class
        ));
        $factory->createService($services->reveal());
    }
}
