<?php

namespace Svycka\SocialUser;

use \Svycka\SocialUser;
use Svycka\SocialUser\OAuth2\GrantType\Facebook;
use Svycka\SocialUser\OAuth2\GrantType\Factory\FacebookFactory;
use Svycka\SocialUser\OAuth2\GrantType\Factory\GoogleFactory;
use Svycka\SocialUser\OAuth2\GrantType\Google;
use Svycka\SocialUser\Service\Factory\SocialUserServiceFactory;
use Svycka\SocialUser\Service\SocialUserService;
use Svycka\SocialUser\Storage\Doctrine;
use Svycka\SocialUser\Storage\Factory\DoctrineStorageFactory;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class ConfigProvider
{
    /**
     * Return configuration for this component.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'svycka_social_user' => $this->getModuleConfig(),
        ];
    }

    /**
     * Return dependency mappings for this component.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'factories' => [
                Facebook::class => FacebookFactory::class,
                Google::class => GoogleFactory::class,
                SocialUserService::class => SocialUserServiceFactory::class,
                Doctrine::class => DoctrineStorageFactory::class,
            ],
        ];
    }

    public function getModuleConfig()
    {
        return [
            // default storage
            'social_user_storage' => Doctrine::class,
            // local user provider name for service manager
            'local_user_provider' => '',
            // default user entity
            'social_user_entity' => SocialUser\Entity\SocialUser::class,
            // optional grant options for all grant types
            'grant_type_options' => [],
        ];
    }
}
