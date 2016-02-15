<?php

namespace Svycka\SocialUser\OAuth2\GrantType;

use GuzzleHttp\Exception\ClientException;
use Svycka\SocialUser\Service\SocialUserService;
use Svycka\SocialUser\UserProfile;
use Svycka\SocialUser\UserProfileInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class Google extends AbstractSocialGrantType
{
    const PROVIDER_NAME = 'google';

    /**
     * @var SocialUserService
     */
    protected $socialUserService;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @var array
     */
    protected $options;

    public function __construct(SocialUserService $socialUserService, \GuzzleHttp\Client $httpClient, array $options)
    {
        if (empty($options['audience'])) {
            throw new \InvalidArgumentException('"audience" option is required but not provided.');
        }

        $this->socialUserService = $socialUserService;
        $this->httpClient        = $httpClient;
        $this->options           = $options;
    }

    public function getQuerystringIdentifier()
    {
        return 'google';
    }

    /**
     * @param string $token
     *
     * @return UserProfileInterface|null
     */
    protected function getTokenInfo($token)
    {
        try {
            $response = $this->httpClient->request('GET', 'https://www.googleapis.com/oauth2/v3/tokeninfo', [
                'query' => [
                    'id_token' => $token,
                ]
            ]);

            $tokenInfo = json_decode($response->getBody()->getContents(), true);

            // check if we can get user identifier
            if (empty($tokenInfo) || empty($tokenInfo['sub'])) {
                return null;
            }

            // do not accept tokens generated not for our application even if they are valid,
            // to protect against "man in the middle" attack
            if ($tokenInfo['aud'] != $this->options['audience']) {
                return null;
            }

            $userProfile = new UserProfile();
            $userProfile->setIdentifier($tokenInfo['sub']);
            $userProfile->setDisplayName(isset($tokenInfo['name']) ? $tokenInfo['name'] : null);
            $userProfile->setFirstName(isset($tokenInfo['given_name']) ? $tokenInfo['given_name'] : null);
            $userProfile->setLastName(isset($tokenInfo['family_name']) ? $tokenInfo['family_name'] : null);
            $userProfile->setEmail(isset($tokenInfo['email']) ? $tokenInfo['email'] : null);
            $userProfile->setEmailVerified(isset($tokenInfo['email_verified']) ? $tokenInfo['email_verified'] : false);

            return $userProfile;
        } catch (ClientException $e) {
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
