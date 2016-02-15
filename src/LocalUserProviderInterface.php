<?php
namespace Svycka\SocialUser;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
interface LocalUserProviderInterface
{
    /**
     * Returns local user ID if exists with provided email.
     *
     * @param string $email
     *
     * @return int|null
     */
    public function findByEmail($email);

    /**
     * Creates new local user and returns its ID.
     * If not possible to create this method should return null.
     *
     * @param UserProfileInterface $userProfile
     *
     * @return int|null
     */
    public function createNewUser(UserProfileInterface $userProfile);
}
