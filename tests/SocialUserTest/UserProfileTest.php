<?php

namespace Svycka\SocialUserTest;

use Svycka\SocialUser\UserProfile;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class UserProfileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserProfile
     */
    private $profile;

    protected function setUp()
    {
        $this->profile = new UserProfile();
    }

    public function testCanSetGetIdentifier()
    {
        $this->profile->setIdentifier('test');
        $this->assertEquals('test', $this->profile->getIdentifier());
    }

    public function testCanSetGetEmail()
    {
        $this->profile->setEmail('user@email.com');
        $this->assertEquals('user@email.com', $this->profile->getEmail());
    }

    public function testCanSetGetDisplayName()
    {
        $this->profile->setDisplayName('Display Name');
        $this->assertEquals('Display Name', $this->profile->getDisplayName());
    }

    public function testCanSetGetFirstName()
    {
        $this->profile->setFirstName('Vytautas');
        $this->assertEquals('Vytautas', $this->profile->getFirstName());
    }

    public function testCanSetGetLastName()
    {
        $this->profile->setLastName('Vytautas');
        $this->assertEquals('Vytautas', $this->profile->getLastName());
    }

    public function testCanSetGetEmailVerified()
    {
        $this->assertFalse($this->profile->isEmailVerified(), "Should be not verified by default");
        $this->profile->setEmailVerified(true);
        $this->assertEquals(true, $this->profile->isEmailVerified());
    }
}
