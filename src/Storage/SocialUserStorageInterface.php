<?php

namespace Svycka\SocialUser\Storage;

use Svycka\SocialUser\Entity\SocialUserInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
interface SocialUserStorageInterface
{
    /**
     * @param string $provider
     * @param string $identifier
     *
     * @return SocialUserInterface|null
     */
    public function findByProviderIdentifier($provider, $identifier);

    /**
     * @param int    $user_id
     * @param string $identifier
     * @param string $provider
     *
     * @return SocialUserInterface
     */
    public function addSocialUser($user_id, $identifier, $provider);
}
