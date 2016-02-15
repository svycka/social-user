<?php

namespace Svycka\SocialUser\Storage\Factory;

use Interop\Container\ContainerInterface;
use Svycka\SocialUser\Storage\Doctrine;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class DoctrineStorageFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $config = $container->get('Config')['svycka_social_user'];
        $options = [
            'social_user_entity' => $config['social_user_entity'],
        ];

        return new Doctrine($entityManager, $options);
    }

    public function createService(ServiceLocatorInterface $services)
    {
        return $this($services, Doctrine::class);
    }
}
