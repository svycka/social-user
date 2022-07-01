<?php

namespace Svycka\SocialUser\OAuth2\GrantType;

use Svycka\SocialUser\Service\SocialUserService;
use Svycka\SocialUser\UserProfile;
use Svycka\SocialUser\UserProfileInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
final class Facebook extends AbstractSocialGrantType
{
    const PROVIDER_NAME = 'facebook';

    public function __construct(
        private SocialUserService $socialUserService,
        private \GuzzleHttp\Client $httpClient,
        private string $appIdentifier,
        private string $appSecret
    ) {
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
            $response = $this->httpClient->request('GET', 'https://graph.facebook.com/debug_token', [
                'query' => [
                    'input_token' => $token,
                    'access_token' => $this->appIdentifier . '|' . $this->appSecret,
                ],
            ]);

            $tokenInfo = json_decode($response->getBody()->getContents(), true);

            // to protect against "man in the middle" attack,
            // do not accept tokens generated not for our application even if they are valid
            if (empty($tokenInfo['data']['app_id']) || $tokenInfo['data']['app_id'] !== $this->appIdentifier) {
                return null;
            }

            // do not allow invalid or expired tokens
            if (empty($tokenInfo['data']['is_valid'])
                || empty($tokenInfo['data']['expires_at'])
                || true !== $tokenInfo['data']['is_valid']
                || time() > $tokenInfo['data']['expires_at']
            ) {
                return null;
            }

            $response = $this->httpClient->request('GET', 'https://graph.facebook.com/me', [
                'query' => [
                    'fields' => 'id,name,email,first_name,last_name',
                    'access_token' => $token,
                ],
            ]);

            $user_info = json_decode($response->getBody()->getContents(), true);

            // check if we can get user identifier
            if (empty($user_info['id'])) {
                return null;
            }

            $userProfile = new UserProfile();
            $userProfile->setIdentifier($user_info['id']);
            $userProfile->setDisplayName($user_info['name'] ?? null);
            $userProfile->setFirstName($user_info['first_name'] ?? null);
            $userProfile->setLastName($user_info['last_name'] ?? null);
            $userProfile->setEmail($user_info['email'] ?? null);
            $userProfile->setEmailVerified(true);

            return $userProfile;
        } catch (ClientException | \RuntimeException $e) {
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
