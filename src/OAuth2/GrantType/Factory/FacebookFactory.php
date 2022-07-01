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
final class FacebookFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $options = $container->get('config')['svycka_social_user']['grant_type_options'];

        if (empty($options[GrantType\Facebook::class])
            || empty($options[GrantType\Facebook::class]['app_id'])
            || empty($options[GrantType\Facebook::class]['app_secret'])
        ) {
            throw new ServiceNotCreatedException('Facebook API options are not set');
        }

        $httpClient = new \GuzzleHttp\Client();
        $socialUserService = $container->get(SocialUserService::class);

        return new GrantType\Facebook(
            $socialUserService,
            $httpClient,
            $options[GrantType\Facebook::class]['app_id'],
            $options[GrantType\Facebook::class]['app_secret']
        );
    }
}
