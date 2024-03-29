<?php

namespace Svycka\SocialUserTest\Storage;

use Doctrine\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Prophecy\Argument\Token\TypeToken;
use Svycka\SocialUser\Entity\SocialUser;
use Svycka\SocialUser\Entity\SocialUserInterface;
use Svycka\SocialUser\Storage\Doctrine;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class DoctrineTest extends \PHPUnit\Framework\TestCase
{
    private $entityManager;

    public function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManager::class);
    }

    public function testCanCreateWithoutOptions()
    {
        new Doctrine($this->entityManager->reveal());

        $this->expectNotToPerformAssertions();
    }

    public function testCanCreateWithOptions()
    {
        $options = [
            'social_user_entity' => SocialUser::class
        ];
        new Doctrine($this->entityManager->reveal(), $options);

        $this->expectNotToPerformAssertions();
    }

    public function testThrowExceptionIfInvalidEntity()
    {
        $options = [
            'social_user_entity' => \stdClass::class
        ];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(sprintf(
            'Configured "social_user_entity" class should implement %s',
            SocialUserInterface::class
        ));
        new Doctrine($this->entityManager->reveal(), $options);
    }

    public function testCanFindSocialUser()
    {
        $repository = $this->prophesize(ObjectRepository::class);
        $repository->findOneBy([
            'provider' => $provider = 'provider',
            'identifier' => $identifier = 'identifier',
        ])->willReturn($result = 'result');
        $this->entityManager->getRepository(SocialUser::class)->willReturn($repository->reveal());

        $storage = new Doctrine($this->entityManager->reveal());

        $this->assertEquals($result, $storage->findByProviderIdentifier($provider, $identifier));
    }

    public function testCanAddSocialUserLogin()
    {
        $this->entityManager->persist(new TypeToken(SocialUserInterface::class))->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $storage = new Doctrine($this->entityManager->reveal());
        $login = $storage->addSocialUser(1, 'id', 'provider');

        $this->assertInstanceOf(SocialUserInterface::class, $login);
        $this->assertEquals(1, $login->getLocalUser());
        $this->assertEquals('id', $login->getIdentifier());
        $this->assertEquals('provider', $login->getProvider());
    }
}
