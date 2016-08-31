<?php

namespace Svycka\SocialUser\OAuth2\GrantType;

use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook as FacebookSDK;
use Svycka\SocialUser\Service\SocialUserService;
use Svycka\SocialUser\UserProfile;
use Svycka\SocialUser\UserProfileInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class Facebook extends AbstractSocialGrantType
{
    const PROVIDER_NAME = 'facebook';

    /**
     * @var SocialUserService
     */
    protected $socialUserService;

    /**
     * @var \Facebook\Facebook
     */
    protected $facebook;

    public function __construct(SocialUserService $socialUserService, FacebookSDK $facebook)
    {
        $this->socialUserService = $socialUserService;
        $this->facebook = $facebook;
    }

    public function getQuerystringIdentifier()
    {
        return 'facebook';
    }

    /**
     * @param string $token
     *
     * @return UserProfileInterface|null
     */
    protected function getTokenInfo($token)
    {
        try {
            // Get the Facebook\GraphNodes\GraphUser object for the current user.
            $response = $this->facebook->get('/me?fields=id,name,email,first_name,last_name', $token);
            $user = $response->getGraphUser();

            // check if we can get user identifier
            if (empty($user->getId())) {
                return null;
            }

            // do not accept tokens generated not for our application even if they are valid,
            // to protect against "man in the middle" attack
            $tokenMetadata = $this->facebook->getOAuth2Client()->debugToken($token);
            // this is not required, but lets be sure because facebook API changes very often
            $tokenMetadata->validateAppId($this->facebook->getApp()->getId());

            $userProfile = new UserProfile();
            $userProfile->setIdentifier($user->getId());
            $userProfile->setDisplayName($user->getName());
            $userProfile->setFirstName($user->getFirstName());
            $userProfile->setLastName($user->getLastName());
            $userProfile->setEmail($user->getEmail());
            // facebook doesn't allow login with not verified email
            if (!empty($user->getEmail())) {
                $userProfile->setEmailVerified(true);
            }

            return $userProfile;
        } catch (FacebookSDKException $e) {
            return null;
        }
    }

    /**
     * @param UserProfileInterface $socialUser
     *
     * @return int|null
     */
    protected function getLocalUser(UserProfileInterface $socialUser)
    {
        return $this->socialUserService->getLocalUser(self::PROVIDER_NAME, $socialUser);
    }
}
