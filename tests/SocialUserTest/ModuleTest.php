<?php

namespace Svycka\SocialUserTest;

use Svycka\SocialUser\ConfigProvider;
use Svycka\SocialUser\Module;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigIsArray()
    {
        $module = new Module();
        $this->assertInternalType('array', $module->getConfig());
    }

    public function testModuleConfigIsSameAsConfigProvider()
    {
        $moduleConfig = (new Module())->getConfig();
        $config = (new ConfigProvider())->__invoke();
        $this->assertEquals($moduleConfig['service_manager'], $config['dependencies']);
        $this->assertEquals($moduleConfig['svycka_social_user'], $config['svycka_social_user']);
    }
}
