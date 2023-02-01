<?php

declare(strict_types=1);

namespace WEBcoast\JwtSso\Middleware;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Authentication\LoginType;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SsoCallbackMiddleware implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected ?AbstractUserAuthentication $beUserBackup = null;

    public const SSO_AUTH_URL = '/sso/auth';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $language = $request->getAttribute('language');
        $uri = new Uri(rtrim((string) $language->getBase(), '/') . self::SSO_AUTH_URL);
        if ($request->getUri()->getPath() === $uri->getPath() && !empty($token = $request->getQueryParams()['token'] ?? null)) {
            $extensionConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('jwt_sso');
            $serverPublicKey = JWKFactory::createFromKeyFile($extensionConfig['server_public_key']);

            $algorithmManager = new AlgorithmManager([new RS256()]);
            $verifier = new JWSVerifier($algorithmManager);
            $serializer = new JWSSerializerManager([new CompactSerializer()]);
            $requestJws = $serializer->unserialize($token);
            $payload = json_decode($requestJws->getPayload(), true);

            if ($verifier->verifyWithKey($requestJws, $serverPublicKey, 0)) {
                $_POST['logintype'] = LoginType::LOGIN;
                $_POST['user'] = 'sso';
                $_POST['pass'] = json_encode($payload);
            }
            // return new RedirectResponse($site->getBase());
        }

        return $handler->handle($request);
    }
}
