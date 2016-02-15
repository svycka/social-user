<?php

namespace Svycka\SocialUser\Service\Factory;

use Interop\Container\ContainerInterface;
use Svycka\SocialUser\LocalUserProviderInterface;
use Svycka\SocialUser\Service\SocialUserService;
use Svycka\SocialUser\Storage\SocialUserStorageInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class SocialUserServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config')['svycka_social_user'];

        $localUserProvider = $container->get($config['local_user_provider']);

        if (!$localUserProvider instanceof LocalUserProviderInterface) {
            throw new ServiceNotCreatedException(sprintf(
                'Invalid "local_user_provider" specified expected class name with implements "%s"',
                LocalUserProviderInterface::class
            ));
        }

        $storage = $container->get($config['social_user_storage']);

        if (!$storage instanceof SocialUserStorageInterface) {
            throw new ServiceNotCreatedException(sprintf(
                'Invalid "social_user_storage" specified expected class name with implements "%s"',
                SocialUserStorageInterface::class
            ));
        }

        return new SocialUserService($localUserProvider, $storage);
    }

    public function createService(ServiceLocatorInterface $services)
    {
        return $this($services, SocialUserService::class);
    }
}
