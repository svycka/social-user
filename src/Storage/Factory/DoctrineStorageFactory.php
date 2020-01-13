<?php

namespace Svycka\SocialUser\Storage\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Svycka\SocialUser\Storage\Doctrine;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
final class DoctrineStorageFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $config = $container->get('config')['svycka_social_user'];
        $options = [
            'social_user_entity' => $config['social_user_entity'],
        ];

        return new Doctrine($entityManager, $options);
    }
}
