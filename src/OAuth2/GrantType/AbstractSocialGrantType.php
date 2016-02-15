<?php

namespace Svycka\SocialUser\OAuth2\GrantType;

use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\ResponseType\AccessTokenInterface;
use Svycka\SocialUser\UserProfileInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
abstract class AbstractSocialGrantType implements GrantTypeInterface
{
    /**
     * @var array
     */
    protected $userInfo;

    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {
        $token = $request->request("token");

        if (!$token) {
            $response->setError(400, 'invalid_request', 'Missing parameter: "token" is required');
            return null;
        }

        $socialUser = $this->getTokenInfo($token);

        if (!$socialUser) {
            $response->setError(401, 'invalid_grant', 'Invalid or expired token');
            return null;
        }

        $user_id = $this->getLocalUser($socialUser);

        if (!$user_id) {
            $response->setError(401, 'invalid_grant', 'Unable to identify or create user');
            return null;
        }

        $this->userInfo = [
            'user_id' => $user_id
        ];

        return true;
    }

    public function getClientId()
    {
        return null;
    }

    public function getUserId()
    {
        return $this->userInfo['user_id'];
    }

    public function getScope()
    {
        return isset($this->userInfo['scope']) ? $this->userInfo['scope'] : null;
    }

    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        return $accessToken->createAccessToken($client_id, $user_id, $scope);
    }

    /**
     * @param string $token
     *
     * @return UserProfileInterface|null
     */
    abstract protected function getTokenInfo($token);

    /**
     * @param UserProfileInterface $socialUser
     *
     * @return int|null
     */
    abstract protected function getLocalUser(UserProfileInterface $socialUser);
}
