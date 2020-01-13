<?php

namespace Svycka\SocialUserTest\OAuth2\GrantType;

use GuzzleHttp\Exception\ClientException;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\ResponseType\AccessTokenInterface;
use Prophecy\Argument\Token\TypeToken;
use Svycka\SocialUser\OAuth2\GrantType\Google;
use Svycka\SocialUser\Service\SocialUserService;
use Svycka\SocialUser\UserProfileInterface;

/**
 * @author  Vytautas Stankus <svycka@gmail.com>
 * @license MIT
 */
class GoogleTest extends \PHPUnit\Framework\TestCase
{
    /** @var SocialUserService */
    private $socialUserService;
    /** @var \GuzzleHttp\Client */
    private $httpClient;
    /** @var RequestInterface */
    private $request;
    /** @var ResponseInterface */
    private $response;
    /** @var Google */
    private $grantType;
    /** @var array */
    private $options;

    protected function setUp()
    {
        $this->socialUserService = $this->prophesize(SocialUserService::class);
        $this->httpClient = $this->prophesize(\GuzzleHttp\Client::class);
        $this->request = $this->prophesize(RequestInterface::class);
        $this->response = $this->prophesize(ResponseInterface::class);

        $this->options = ['audience' => '1008719970978-hb24n2dstb40o45d4feuo2ukqmcc6381.apps.googleusercontent.com'];
        $this->grantType = new Google($this->socialUserService->reveal(), $this->httpClient->reveal(), $this->options);
    }

    public function testWillThrowExceptionIfApplicationIdIsNotProvided()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"audience" option is required but not provided.');
        $this->grantType = new Google($this->socialUserService->reveal(), new \GuzzleHttp\Client, []);
    }

    public function testErrorMissingToken()
    {
        $this->request->request('token')->willReturn(null);
        $this->response->setError(400, 'invalid_request', 'Missing parameter: "token" is required')->shouldBeCalled();

        $this->assertNull($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
    }

    public function testErrorInvalidToken()
    {
        $this->request->request('token')->willReturn('token');
        $this->httpClient->request('GET', 'https://www.googleapis.com/oauth2/v3/tokeninfo', [
            'query' => [
                'id_token' => 'token',
            ],
        ])->willThrow(ClientException::class);
        $this->response->setError(401, 'invalid_grant', 'Invalid or expired token')->shouldBeCalled();

        $this->assertNull($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
    }

    public function testWillNotAuthorizeIfSocialUserIdUnknown()
    {
        $this->request->request('token')->willReturn('token');
        $this->setSocialApiResponse('');

        $this->response->setError(401, 'invalid_grant', 'Invalid or expired token')->shouldBeCalled();

        $this->assertNull($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
    }

    public function testDoesNotAllowTokenGeneratedNotForConfiguredApplication()
    {
        $this->request->request('token')->willReturn('token');
        $tokenInfo = json_decode($this->tokenInfo(), true);
        $tokenInfo['aud'] = 'man-in-the-middle-attack';
        $this->setSocialApiResponse(json_encode($tokenInfo));

        $this->response->setError(401, 'invalid_grant', 'Invalid or expired token')->shouldBeCalled();

        $this->assertNull($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
    }

    public function testCanAuthenticateAgainstGoogleButCantGetOrCreateLocalUser()
    {
        $this->request->request('token')->willReturn('token');
        $this->setSocialApiResponse($this->tokenInfo());
        $this->socialUserService->getLocalUser(Google::PROVIDER_NAME, new TypeToken(UserProfileInterface::class))
            ->willReturn(null);
        $this->response->setError(401, 'invalid_grant', 'Unable to identify or create user')->shouldBeCalled();

        $this->assertNull($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
    }

    public function testCanAuthenticate()
    {
        $this->request->request('token')->willReturn('token');
        $this->setSocialApiResponse($this->tokenInfo());

        $this->socialUserService->getLocalUser(Google::PROVIDER_NAME, new TypeToken(UserProfileInterface::class))
            ->willReturn(123)->shouldBeCalled();

        $this->assertTrue($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
        $this->assertEquals('google', $this->grantType->getQuerystringIdentifier());
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

    public function testCanAuthenticateWithoutAdditionalPermissions()
    {
        $this->request->request('token')->willReturn('token');
        $tokenInfo = json_encode([
            // These six fields are included in all Google ID Tokens.
            "iss" => "https://accounts.google.com",
            "sub" => "110169484474386276334",
            "azp" => "1008719970978-hb24n2dstb40o45d4feuo2ukqmcc6381.apps.googleusercontent.com",
            "aud" => "1008719970978-hb24n2dstb40o45d4feuo2ukqmcc6381.apps.googleusercontent.com",
            "iat" => "1433978353",
            "exp" => "1433981953",
        ]);
        $this->setSocialApiResponse($tokenInfo);

        $this->socialUserService->getLocalUser(Google::PROVIDER_NAME, new TypeToken(UserProfileInterface::class))
            ->willReturn(123)->shouldBeCalled();

        $this->assertTrue($this->grantType->validateRequest($this->request->reveal(), $this->response->reveal()));
        $this->assertEquals('google', $this->grantType->getQuerystringIdentifier());
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

    private function tokenInfo()
    {
        return json_encode([
            // These six fields are included in all Google ID Tokens.
            "iss" => "https://accounts.google.com",
            "sub" => "110169484474386276334",
            "azp" => "1008719970978-hb24n2dstb40o45d4feuo2ukqmcc6381.apps.googleusercontent.com",
            "aud" => "1008719970978-hb24n2dstb40o45d4feuo2ukqmcc6381.apps.googleusercontent.com",
            "iat" => "1433978353",
            "exp" => "1433981953",

            // These seven fields are only included when the user has granted the "profile" and
            // "email" OAuth scopes to the application.
            "email" => "testuser@gmail.com",
            "email_verified" => "true",
            "name" => "Test User",
            "picture" => "https://lh4.googleusercontent.com/-kYgzyAWpZzJ/ABCD"
                . "EFGHI/AAAJKLMNOP/tIXL9Ir44LE/s99-c/photo.jpg",
            "given_name" => "Test",
            "family_name" => "User",
            "locale" => "en",
        ]);
    }

    private function setSocialApiResponse($content, $http_status = 200)
    {
        $this->httpClient->request('GET', 'https://www.googleapis.com/oauth2/v3/tokeninfo', [
            'query' => [
                'id_token' => 'token',
            ],
        ])->willReturn(new \GuzzleHttp\Psr7\Response($http_status, [], $content));
    }
}
