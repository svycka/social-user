<?php

namespace Svycka\SocialUserTest\OAuth2\GrantType;

use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook as FacebookSDK;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\ResponseType\AccessTokenInterface;
use Prophecy\Argument\Token\TypeToken;
use Svycka\SocialUser\OAuth2\GrantType\Facebook;
use Svycka\SocialUser\Service\SocialUserService;
use Svycka\SocialUser\UserProfileInterface;

/**
 * @author Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class FacebookTest extends \PHPUnit_Framework_TestCase
{
    /** @var SocialUserService */
    private $socialUserService;
    /** @var FacebookSDK */
    private $facebook;
    /** @var RequestInterface */
    private $request;
    /** @var ResponseInterface */
    private $response;
    /** @var Facebook */
    private $grantType;

    protected function setUp()
    {
        $this->socialUserService = $this->prophesize(SocialUserService::class);
        $this->facebook = $this->prophesize(FacebookSDK::class);
        $this->request = $this->prophesize(RequestInterface::class);
        $this->response = $this->prophesize(ResponseInterface::class);
        $this->grantType = new Facebook($this->socialUserService->reveal(), $this->facebook->reveal());
    }

    public function testErrorMissingToken()
    {
        $this->request->request('token')->willReturn(null);
        $this->response->setError(400, 'invalid_request', 'Missing parameter: "token" is required')->shouldBeCalled();

        $this->assertNull($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
    }

    public function testErrorInvalidToken()
    {
        $this->request->request('token')->willReturn('facebook_access_token');
        $this->facebook->get('/me?fields=id,name,email,first_name,last_name', 'facebook_access_token')
            ->willThrow(FacebookSDKException::class);
        $this->response->setError(401, 'invalid_grant', 'Invalid or expired token')->shouldBeCalled();

        $this->assertNull($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
    }

    public function testWillNotAuthorizeIfSocialUserIdUnknown()
    {
        $this->request->request('token')->willReturn('facebook_access_token');
        $graphUser = $this->prophesize(\Facebook\GraphNodes\GraphUser::class);
        $graphUser->getId()->willReturn(null);

        $this->setFacebookApiResponse($graphUser->reveal());
        $this->response->setError(401, 'invalid_grant', 'Invalid or expired token')->shouldBeCalled();

        $this->assertNull($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
    }

    public function testCanAuthenticateAgainstFacebookButCantGetOrCreateLocalUser()
    {
        $this->request->request('token')->willReturn('facebook_access_token');
        $this->setFacebookApiResponse($this->getGraphUser());
        $this->socialUserService->getLocalUser(Facebook::PROVIDER_NAME, new TypeToken(UserProfileInterface::class))
            ->willReturn(null);
        $this->response->setError(401, 'invalid_grant', 'Unable to identify or create user')->shouldBeCalled();

        $this->assertNull($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
    }

    public function testCanAuthenticate()
    {
        $this->request->request('token')->willReturn('facebook_access_token');
        $this->setFacebookApiResponse($this->getGraphUser());

        $this->socialUserService->getLocalUser(Facebook::PROVIDER_NAME, new TypeToken(UserProfileInterface::class))
            ->willReturn(123)->shouldBeCalled();

        $this->assertTrue($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
        $this->assertEquals('facebook', $this->grantType->getQuerystringIdentifier());
        $this->assertEquals(123, $this->grantType->getUserId());

        $accessToken = $this->prophesize(AccessTokenInterface::class);
        $accessToken->createAccessToken(
            $this->grantType->getClientId(),
            $this->grantType->getUserId(),
            $this->grantType->getScope()
        )->shouldBeCalled();
        $this->grantType->createAccessToken(
            $accessToken->reveal(),
            $this->grantType->getClientId(),
            $this->grantType->getUserId(),
            $this->grantType->getScope()
        );
    }

    public function getGraphUser()
    {
        $graphUser = $this->prophesize(\Facebook\GraphNodes\GraphUser::class);
        $graphUser->getId()->willReturn('social_id');
        $graphUser->getName()->willReturn('name');
        $graphUser->getFirstName()->willReturn('first');
        $graphUser->getLastName()->willReturn('last');
        $graphUser->getEmail()->willReturn('user@mail.com');

        return $graphUser->reveal();
    }

    public function setFacebookApiResponse($graphUser)
    {
        $response = $this->prophesize(\Facebook\FacebookResponse::class);
        $response->getGraphUser()->willReturn($graphUser);
        $this->facebook->get('/me?fields=id,name,email,first_name,last_name', 'facebook_access_token')
            ->willReturn($response);
    }
}
