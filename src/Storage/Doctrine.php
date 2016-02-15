<?php

namespace Svycka\SocialUser\Storage;

use Doctrine\ORM\EntityManager;
use Svycka\SocialUser\Entity\SocialUser;
use Svycka\SocialUser\Entity\SocialUserInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class Doctrine implements SocialUserStorageInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Social user Entity class name
     *
     * @var string
     */
    protected $entity = SocialUser::class;

    public function __construct(EntityManager $entityManager, array $options = null)
    {
        $options = empty($options) ? [] : $options;

        if (array_key_exists('social_user_entity', $options)) {
            if (!is_subclass_of($options['social_user_entity'], SocialUserInterface::class)) {
                throw new \Exception(
                    sprintf('Configured "social_user_entity" class should implement %s', SocialUserInterface::class)
                );
            }
            $this->entity = $options['social_user_entity'];
        }

        $this->em = $entityManager;
    }

    /**
     * @param string $provider
     * @param string $identifier
     *
     * @return SocialUserInterface|null
     */
    public function findByProviderIdentifier($provider, $identifier)
    {
        $repository = $this->em->getRepository($this->entity);

        return $repository->findOneBy([
            'provider' => $provider,
            'identifier' => $identifier,
        ]);
    }

    /**
     * @param int    $user_id
     * @param string $identifier
     * @param string $provider
     *
     * @return SocialUserInterface
     */
    public function addSocialUser($user_id, $identifier, $provider)
    {
        /** @var SocialUserInterface $socialUser */
        $socialUser = new $this->entity();
        $socialUser->setLocalUser($user_id);
        $socialUser->setIdentifier($identifier);
        $socialUser->setProvider($provider);

        $this->em->persist($socialUser);
        $this->em->flush($socialUser);

        return $socialUser;
    }
}
