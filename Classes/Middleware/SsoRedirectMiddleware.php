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
use TYPO3\CMS\Core\Session\UserSessionManager;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SsoRedirectMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUri()->getPath() === '/login') {
            // Login is explicitly requested
            return $this->redirectToSso($request);
        } else {
            $sessionManager = UserSessionManager::create('FE');
            $session = $sessionManager->createFromRequestOrAnonymous($request, $GLOBALS['TYPO3_CONF_VARS']['FE']['cookieName']);
            if ($sessionManager->hasExpired($session)) {
                // Session is expired, re-authenticate with the SSO backend
                return $this->redirectToSso($request);
            }
        }

        return $handler->handle($request);
    }

    protected function redirectToSso(ServerRequestInterface $request): RedirectResponse
    {
        $site = $request->getAttribute('site');
        $extensionConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('jwt_sso');
        $clientPrivateKey = JWKFactory::createFromKeyFile($extensionConfig['client_private_key']);
        $referrer = $request->getHeader('Referer')[0] ?? null;

        $builder = new JWSBuilder(new AlgorithmManager([new RS256()]));
        $jws = $builder->create()
            ->withPayload(json_encode(['iss' => $extensionConfig['client_identifier'], 'action' => 'login', 'redirect_url' => $site->getBase() . 'sso/auth?token=%s&redirect_url=' . ($referrer ?? (string) $site->getBase())]))
            ->addSignature($clientPrivateKey, ['alg' => 'RS256'])
            ->build();

        $token = (new CompactSerializer())->serialize($jws, 0);

        return new RedirectResponse(sprintf($extensionConfig['server_sso_entry_point'], $token));
    }
}
