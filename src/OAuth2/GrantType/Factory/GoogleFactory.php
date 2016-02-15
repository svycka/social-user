<?php

namespace Svycka\SocialUser\OAuth2\GrantType\Factory;

use Svycka\SocialUser\OAuth2\GrantType;
use Interop\Container\ContainerInterface;
use Svycka\SocialUser\Service\SocialUserService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class GoogleFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $options = $container->get('Config')['svycka_social_user']['grant_type_options'];

        if (empty($options[GrantType\Google::class])) {
            throw new ServiceNotCreatedException(sprintf('"%s" options not set', GrantType\Google::class));
        }

        $httpClient = new \GuzzleHttp\Client();
        $socialUserService = $container->get(SocialUserService::class);

        return new GrantType\Google($socialUserService, $httpClient, $options[GrantType\Google::class]);
    }

    public function createService(ServiceLocatorInterface $services)
    {
        return $this($services, GrantType\Google::class);
    }
}
