<?php

namespace Svycka\SocialUserTest;

use Svycka\SocialUser\ConfigProvider;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class ConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testConfigIsArray()
    {
        $configProvider = new ConfigProvider();
        $this->assertInternalType('array', $configProvider->__invoke());
    }
}
