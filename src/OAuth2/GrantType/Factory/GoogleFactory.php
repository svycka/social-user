<?php

namespace Svycka\SocialUser\OAuth2\GrantType\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Svycka\SocialUser\OAuth2\GrantType;
use Svycka\SocialUser\Service\SocialUserService;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
final class GoogleFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $options = $container->get('config')['svycka_social_user']['grant_type_options'];

        if (empty($options[GrantType\Google::class])) {
            throw new ServiceNotCreatedException(sprintf('"%s" options not set', GrantType\Google::class));
        }

        $httpClient = new \GuzzleHttp\Client();
        $socialUserService = $container->get(SocialUserService::class);

        return new GrantType\Google($socialUserService, $httpClient, $options[GrantType\Google::class]);
    }
}
