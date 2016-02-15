<?php

namespace Svycka\SocialUser\Service;

use Svycka\SocialUser\LocalUserProviderInterface;
use Svycka\SocialUser\Storage\SocialUserStorageInterface;
use Svycka\SocialUser\UserProfileInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class SocialUserService
{
    /**
     * @var LocalUserProviderInterface
     */
    private $localUserProvider;

    /**
     * @var SocialUserStorageInterface
     */
    private $socialUserStorage;

    public function __construct(
        LocalUserProviderInterface $localUserProvider,
        SocialUserStorageInterface $socialUserStorage
    ) {
        $this->localUserProvider = $localUserProvider;
        $this->socialUserStorage = $socialUserStorage;
    }

    /**
     * Gets local user ID.
     *
     * First tries to find already existing social login,
     * then tries to merge to existing user by comparing email addresses
     * and finally will create new user if earlier scenarios failed.
     *
     * So in the end should return existing or newly created local user ID or if fails return null.
     *
     * @param string               $provider    Provider name.
     * @param UserProfileInterface $userProfile User identifier used with provider.
     *
     * @return int|null
     */
    public function getLocalUser($provider, UserProfileInterface $userProfile)
    {
        if (empty($provider) || !is_string($provider)) {
            throw new \InvalidArgumentException('Invalid "provider" argument provided.');
        }

        // if possible will use social login data to retrieve user ID
        $result = $this->socialUserStorage->findByProviderIdentifier($provider, $userProfile->getIdentifier());
        if ($result) {
            return $result->getLocalUser();
        }

        // only reach below if we do not have this social user

        // We have to be sure here that email exists and is verified, otherwise we can't assume it's the same user.
        if (empty($userProfile->getEmail()) || !$userProfile->isEmailVerified()) {
            return null;
        }

        // Will try to find and merge to existing user by email address
        $user_id = $this->localUserProvider->findByEmail($userProfile->getEmail());

        // if user with same email doesn't exist in the database then will create new local user
        if (!$user_id) {
            $user_id = $this->localUserProvider->createNewUser($userProfile);
        }

        // if same email address exists in database then add new social login for that user
        if ($user_id) {
            $this->socialUserStorage->addSocialUser($user_id, $userProfile->getIdentifier(), $provider);
        }

        return $user_id;
    }
}
