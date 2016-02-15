<?php

namespace Svycka\SocialUserTest;

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
}
