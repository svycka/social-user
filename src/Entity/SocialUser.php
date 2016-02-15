<?php

namespace Svycka\SocialUser\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 *
 * @ORM\Entity
 * @ORM\Table(name="social_user_providers", options={"collate"="utf8mb4_unicode_ci", "charset"="utf8mb4"})
 */
class SocialUser implements SocialUserInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $localUser;

    /**
     * @var string
     * @ORM\Column(type="string", length=191)
     */
    protected $identifier;

    /**
     * @var string
     * @ORM\Column(type="string", length=191)
     */
    protected $provider;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = (int) $id;
    }

    /**
     * @param int $user
     */
    public function setLocalUser($user)
    {
        $this->localUser = $user;
    }

    /**
     * @return int
     */
    public function getLocalUser()
    {
        return $this->localUser;
    }

    /**
     * Get user identifier from provider.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set user identifier from provider.
     *
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Get provider identifier(name).
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set provider identifier(name).
     *
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }
}
