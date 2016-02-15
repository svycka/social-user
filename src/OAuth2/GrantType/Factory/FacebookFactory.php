<?php

namespace Svycka\SocialUser\OAuth2\GrantType\Factory;

use Facebook\Facebook;
use Svycka\SocialUser\OAuth2\GrantType\Facebook as FacebookGrantType;
use Interop\Container\ContainerInterface;
use Svycka\SocialUser\Service\SocialUserService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class FacebookFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $options = $container->get('Config')['svycka_social_user']['grant_type_options'];

        if (empty($options[FacebookGrantType::class])) {
            throw new ServiceNotCreatedException('Facebook API options are not set');
        }

        $facebook = new Facebook($options[FacebookGrantType::class]);
        $socialUserService = $container->get(SocialUserService::class);

        return new FacebookGrantType($socialUserService, $facebook);
    }

    public function createService(ServiceLocatorInterface $services)
    {
        return $this($services, FacebookGrantType::class);
    }
}
