<?php

use \Svycka\SocialUser;

return [
    'svycka_social_user' => [
        // default storage
        'social_user_storage' => SocialUser\Storage\Doctrine::class,
        // local user provider name for service manager
        'local_user_provider' => '',
        // default user entity
        'social_user_entity' => SocialUser\Entity\SocialUser::class,
        // optional grant options for all grant types
        'grant_type_options' => [],
    ],
    'service_manager' => [
        'factories' => [
            SocialUser\OAuth2\GrantType\Facebook::class => SocialUser\OAuth2\GrantType\Factory\FacebookFactory::class,
            SocialUser\OAuth2\GrantType\Google::class   => SocialUser\OAuth2\GrantType\Factory\GoogleFactory::class,
            SocialUser\Service\SocialUserService::class => SocialUser\Service\Factory\SocialUserServiceFactory::class,
            SocialUser\Storage\Doctrine::class          => SocialUser\Storage\Factory\DoctrineStorageFactory::class,
        ]
    ]
];
