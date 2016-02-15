<?php
namespace Svycka\SocialUser\Entity;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
interface SocialUserInterface
{
    public function setLocalUser($id);

    /**
     * @return int
     */
    public function getLocalUser();

    /**
     * @param string $provider
     */
    public function setProvider($provider);

    /**
     * @return string
     */
    public function getProvider();

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier);

    /**
     * @return string
     */
    public function getIdentifier();
}
