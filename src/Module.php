<?php

namespace Svycka\SocialUser;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class Module
{
    public function getConfig()
    {
        $configProvider = new ConfigProvider();

        return [
            'service_manager' => $configProvider->getDependencyConfig(),
            'svycka_social_user' => $configProvider->getModuleConfig(),
        ];
    }
}
