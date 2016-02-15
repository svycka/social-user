<?php

namespace Svycka\SocialUserTest\Storage\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Svycka\SocialUser\Entity\SocialUser;
use Svycka\SocialUser\Storage\Doctrine;
use Svycka\SocialUser\Storage\Factory\DoctrineStorageFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class DoctrineStorageFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreate()
    {
        $services = $this->prophesize(ServiceLocatorInterface::class);
        $services->willImplement(ContainerInterface::class);
        $services->get('Config')->willReturn([
            'svycka_social_user' => [
                'social_user_entity' => SocialUser::class
            ]
        ]);

        $storage = $this->prophesize(EntityManager::class);
        $services->get("doctrine.entitymanager.orm_default")->willReturn($storage->reveal());

        $factory = new DoctrineStorageFactory();
        $storage = $factory->createService($services->reveal());
        $this->assertInstanceOf(Doctrine::class, $storage);
    }
}
