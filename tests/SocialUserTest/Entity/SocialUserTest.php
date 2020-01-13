<?php

namespace Svycka\SocialUserTest\Entity;

use Svycka\SocialUser\Entity\SocialUser;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class SocialUserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SocialUser
     */
    public $entity;

    protected function setUp()
    {
        $this->entity = new SocialUser();
    }

    public function testCanSetGetId()
    {
        $this->entity->setId(5);
        $this->assertEquals(5, $this->entity->getId());
    }

    public function testCanSetGetProvider()
    {
        $this->entity->setProvider('test');
        $this->assertEquals('test', $this->entity->getProvider());
    }

    public function testCanSetGetIdentifier()
    {
        $this->entity->setIdentifier('test');
        $this->assertEquals('test', $this->entity->getIdentifier());
    }

    public function testCanSetGetLocalUser()
    {
        $this->entity->setLocalUser(6);
        $this->assertEquals(6, $this->entity->getLocalUser());
    }
}
