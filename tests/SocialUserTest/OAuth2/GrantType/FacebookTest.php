<?php

namespace Svycka\SocialUserTest\OAuth2\GrantType;

use GuzzleHttp\Exception\ClientException;
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
class FacebookTest extends \PHPUnit\Framework\TestCase
{
    const APP_ID = 'FacebookAppID';
    const APP_SECRET = 'FacebookAppSecret';

    /** @var SocialUserService */
    private $socialUserService;
    /** @var \GuzzleHttp\Client */
    private $httpClient;
    /** @var RequestInterface */
    private $request;
    /** @var ResponseInterface */
    private $response;
    /** @var Facebook */
    private $grantType;

    protected function setUp(): void
    {
        $this->socialUserService = $this->prophesize(SocialUserService::class);
        $this->httpClient = $this->prophesize(\GuzzleHttp\Client::class);
        $this->request = $this->prophesize(RequestInterface::class);
        $this->response = $this->prophesize(ResponseInterface::class);
        $this->grantType = new Facebook(
            $this->socialUserService->reveal(),
            $this->httpClient->reveal(),
            self::APP_ID,
            self::APP_SECRET
        );
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
        $this->httpClient->request('GET', 'https://graph.facebook.com/debug_token', [
            'query' => [
                'input_token' => 'facebook_access_token',
                'access_token' => self::APP_ID . '|' . self::APP_SECRET,
            ],
        ])->willThrow(ClientException::class);
        $this->response->setError(401, 'invalid_grant', 'Invalid or expired token')->shouldBeCalled();

        $this->assertNull($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
    }

    public function testWillNotAuthorizeIfSocialUserIdUnknown()
    {
        $this->request->request('token')->willReturn('facebook_access_token');
        $this->withValidToken();
        $this->setFacebookApiResponse('');
        $this->response->setError(401, 'invalid_grant', 'Invalid or expired token')->shouldBeCalled();

        $this->assertNull($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
    }

    public function testDoesNotAllowTokenFromOtherApplications()
    {
        $this->request->request('token')->willReturn('facebook_access_token');
        $this->httpClient->request('GET', 'https://graph.facebook.com/debug_token', [
            'query' => [
                'input_token' => 'facebook_access_token',
                'access_token' => self::APP_ID . '|' . self::APP_SECRET,
            ],
        ])->willReturn(new \GuzzleHttp\Psr7\Response(200, [], json_encode([
            'data' => [
                'app_id' => '1323',
                'is_valid' => true,
                'expires_at' => time()+5,
            ]
        ])));
        $this->response->setError(401, 'invalid_grant', 'Invalid or expired token')->shouldBeCalled();

        $this->assertNull($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
    }

    public function testCanAuthenticateAgainstFacebookButCantGetOrCreateLocalUser()
    {
        $this->request->request('token')->willReturn('facebook_access_token');
        $this->withValidToken();
        $this->setFacebookApiResponse($this->validUserInfoResponse());
        $this->socialUserService->getLocalUser(Facebook::PROVIDER_NAME, new TypeToken(UserProfileInterface::class))
            ->willReturn(null);
        $this->response->setError(401, 'invalid_grant', 'Unable to identify or create user')->shouldBeCalled();

        $this->assertNull($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
    }

    public function testCanAuthenticate()
    {
        $this->request->request('token')->willReturn('facebook_access_token');
        $this->withValidToken();
        $this->setFacebookApiResponse($this->validUserInfoResponse());

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

    public function withValidToken()
    {
        $this->httpClient->request('GET', 'https://graph.facebook.com/debug_token', [
            'query' => [
                'input_token' => 'facebook_access_token',
                'access_token' => self::APP_ID . '|' . self::APP_SECRET,
            ],
        ])->willReturn(new \GuzzleHttp\Psr7\Response(200, [], json_encode([
            'data' => [
                'app_id' => self::APP_ID,
                'is_valid' => true,
                'expires_at' => time()+5,
            ]
        ])));
    }


    public function setFacebookApiResponse($content, int $http_status = 200)
    {
        $this->httpClient->request('GET', 'https://graph.facebook.com/me', [
            'query' => [
                'fields' => 'id,name,email,first_name,last_name',
                'access_token' => 'facebook_access_token',
            ],
        ])->willReturn(new \GuzzleHttp\Psr7\Response($http_status, [], $content));
    }

    private function validUserInfoResponse(): string
    {
        return json_encode([
            'id' => 'social_id',
            'name' => 'name',
            'first_name' => 'first',
            'last_name' => 'last',
            'email' => 'user@mail.com',
        ]);
    }
}
