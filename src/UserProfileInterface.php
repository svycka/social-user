<?php

namespace Svycka\SocialUser;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
interface UserProfileInterface
{
    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier);

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @param string $email
     */
    public function setEmail($email);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $displayName
     */
    public function setDisplayName($displayName);

    /**
     * @return string|null
     */
    public function getDisplayName();

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName);

    /**
     * @return string|null
     */
    public function getFirstName();

    /**
     * @param string$lastName
     */
    public function setLastName($lastName);

    /**
     * @return string|null
     */
    public function getLastName();

    /**
     * @param bool $verified
     */
    public function setEmailVerified($verified);

    /**
     * Status from social service about email being verified ownership.
     * Defaults to false for security reasons.
     *
     * @return bool
     */
    public function isEmailVerified();
}
