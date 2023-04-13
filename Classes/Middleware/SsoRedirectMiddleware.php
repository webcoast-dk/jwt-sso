<?php

declare(strict_types=1);

namespace WEBcoast\JwtSso\Middleware;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Session\UserSessionManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SsoRedirectMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $language = $request->getAttribute('language');
        if ($language) {
            $loginUri = new Uri(rtrim((string) $language->getBase(), '/') . '/' . ltrim($language->toArray()['jwtsso_login_route'], '/'));
            $callbackUri = new Uri(rtrim((string) $language->getBase(), '/') . SsoCallbackMiddleware::SSO_AUTH_URL);
            if ($request->getUri()->getPath() === $loginUri->getPath()) {
                // Login is explicitly requested
                return $this->redirectToSso($request);
            } elseif ($request->getUri()->getPath() !== $callbackUri->getPath()) {
                $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('jwt_sso');
                if ($extensionConfiguration['enableRedirectOnExpiredSession']) {
                    $sessionManager = UserSessionManager::create('FE');
                    $session = $sessionManager->createFromRequestOrAnonymous($request, $GLOBALS['TYPO3_CONF_VARS']['FE']['cookieName']);
                    if ($sessionManager->hasExpired($session)) {
                        // Session is expired, re-authenticate with the SSO backend
                        return $this->redirectToSso($request);
                    }
                }
            }
        }

        return $handler->handle($request);
    }

    protected function redirectToSso(ServerRequestInterface $request): RedirectResponse
    {
        $language = $request->getAttribute('language');
        $extensionConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('jwt_sso');
        $clientPrivateKey = JWKFactory::createFromKeyFile($extensionConfig['client_private_key']);
        $referrer = $request->getHeader('Referer')[0] ?? null;

        $builder = new JWSBuilder(new AlgorithmManager([new RS256()]));
        $jws = $builder->create()
            ->withPayload(json_encode(['iss' => $extensionConfig['client_identifier'], 'action' => 'login', 'redirect_url' => rtrim((string) $language->getBase(), '/') . SsoCallbackMiddleware::SSO_AUTH_URL . '?token=%s&redirect_url=' . ($referrer ?? (string) $language->getBase())]))
            ->addSignature($clientPrivateKey, ['alg' => 'RS256'])
            ->build();

        $token = (new CompactSerializer())->serialize($jws, 0);

        return new RedirectResponse(sprintf($extensionConfig['server_sso_entry_point'], $token));
    }
}
