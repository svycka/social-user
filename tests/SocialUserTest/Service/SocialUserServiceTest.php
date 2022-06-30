<?php

namespace Svycka\SocialUserTest\Settings\Service;

use Svycka\SocialUser\Entity\SocialUser;
use Svycka\SocialUser\LocalUserProviderInterface;
use Svycka\SocialUser\Service\SocialUserService;
use Svycka\SocialUser\Storage\SocialUserStorageInterface;
use Svycka\SocialUser\UserProfile;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class SocialUserServiceTest extends \PHPUnit\Framework\TestCase
{
    /** @var SocialUser */
    private $socialUser;
    /** @var UserProfile */
    private $userProfile;
    /** @var LocalUserProviderInterface */
    private $localUserProvider;
    /** @var SocialUserStorageInterface */
    private $socialUserStorage;

    public function setUp(): void
    {
        $socialUser = new SocialUser();
        $socialUser->setIdentifier('identifier');
        $socialUser->setLocalUser(5);
        $socialUser->setProvider('provider');
        $this->socialUser = $socialUser;

        $userProfile = new UserProfile();
        $userProfile->setIdentifier($socialUser->getIdentifier());
        $userProfile->setEmail('user@email.com');
        $userProfile->setDisplayName('Display Name');
        $userProfile->setFirstName('Vytautas');
        $userProfile->setLastName('Stankus');
        $userProfile->setEmailVerified(true);

        $this->userProfile       = $userProfile;
        $this->localUserProvider = $this->prophesize(LocalUserProviderInterface::class);
        $this->socialUserStorage = $this->prophesize(SocialUserStorageInterface::class);
    }

    public function testShouldThrowExceptionIfInvalidProvider()
    {
        $service = new SocialUserService($this->localUserProvider->reveal(), $this->socialUserStorage->reveal());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid "provider" argument provided.');
        $service->getLocalUser(0, $this->userProfile);
    }

    public function testCanGetExistingSocialUser()
    {
        $this->socialUserStorage
            ->findByProviderIdentifier($this->socialUser->getProvider(), $this->userProfile->getIdentifier())
            ->willReturn($this->socialUser)->shouldBeCalled();
        $service = new SocialUserService($this->localUserProvider->reveal(), $this->socialUserStorage->reveal());

        $user_id = $service->getLocalUser($this->socialUser->getProvider(), $this->userProfile);

        $this->assertEquals($this->socialUser->getLocalUser(), $user_id);
    }

    public function testWillNotCreateNewUserIfEmailNotSet()
    {
        $this->userProfile->setEmail(null);
        $this->socialUserDoesNotExist();

        $service = new SocialUserService($this->localUserProvider->reveal(), $this->socialUserStorage->reveal());

        $user_id = $service->getLocalUser($this->socialUser->getProvider(), $this->userProfile);

        $this->assertNull($user_id, 'Should not create account if email is not set');
    }

    public function testWillNotCreateNewUserIfEmailNotVerified()
    {
        $this->userProfile->setEmailVerified(false);
        $this->socialUserDoesNotExist();

        $service = new SocialUserService($this->localUserProvider->reveal(), $this->socialUserStorage->reveal());

        $user_id = $service->getLocalUser($this->socialUser->getProvider(), $this->userProfile);

        $this->assertNull($user_id, 'Should not create account if email is set but not verified');
    }

    public function testCanAddNewSocialUserProvider()
    {
        $this->socialUserDoesNotExist();
        $this->willFindLocalUserByEmail($this->socialUser->getLocalUser());
        $this->willAddSocialUserLogin();

        $service = new SocialUserService($this->localUserProvider->reveal(), $this->socialUserStorage->reveal());

        $user_id = $service->getLocalUser($this->socialUser->getProvider(), $this->userProfile);

        $this->assertEquals($this->socialUser->getLocalUser(), $user_id);
    }

    public function testCanCreateNewLocalUser()
    {
        $this->socialUserDoesNotExist();
        $this->willFindLocalUserByEmail(null);
        $this->willCallCreateNewLocalUser($this->socialUser->getLocalUser());
        $this->willAddSocialUserLogin();

        $service = new SocialUserService($this->localUserProvider->reveal(), $this->socialUserStorage->reveal());

        $user_id = $service->getLocalUser($this->socialUser->getProvider(), $this->userProfile);

        $this->assertEquals($this->socialUser->getLocalUser(), $user_id);
    }

    public function testWillReturnNullIfCantCreateLocalUser()
    {
        $this->socialUserDoesNotExist();
        $this->willFindLocalUserByEmail(null);
        $this->willCallCreateNewLocalUser(null);

        $service = new SocialUserService($this->localUserProvider->reveal(), $this->socialUserStorage->reveal());

        $user_id = $service->getLocalUser($this->socialUser->getProvider(), $this->userProfile);

        $this->assertNull($user_id, 'Should return null if not possible to create new local user');
    }
    private function socialUserDoesNotExist()
    {
        $this->socialUserStorage
            ->findByProviderIdentifier($this->socialUser->getProvider(), $this->userProfile->getIdentifier())
            ->willReturn(null)->shouldBeCalled();
    }

    private function willCallCreateNewLocalUser($return)
    {
        $this->localUserProvider->createNewUser($this->userProfile)
            ->willReturn($return)->shouldBeCalled();
    }

    private function willFindLocalUserByEmail($user)
    {
        $this->localUserProvider->findByEmail($this->userProfile->getEmail())
            ->willReturn($user)->shouldBeCalled();
    }

    private function willAddSocialUserLogin()
    {
        $this->socialUserStorage->addSocialUser(
            $this->socialUser->getLocalUser(),
            $this->userProfile->getIdentifier(),
            $this->socialUser->getProvider()
        )->willReturn($this->socialUser)->shouldBeCalled();
    }
}
