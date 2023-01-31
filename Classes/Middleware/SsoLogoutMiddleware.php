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

class SsoLogoutMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUri()->getPath() === '/logout') {
            $sessionManager = UserSessionManager::create('FE');
            $session = $sessionManager->createFromRequestOrAnonymous($request, $GLOBALS['TYPO3_CONF_VARS']['FE']['cookieName']);
            $sessionManager->removeSession($session);
            $referrer = $request->getHeader('Referer')[0];
            $site = $request->getAttribute('site');

            return new RedirectResponse($referrer ?? $site->getBase());
        }

        return $handler->handle($request);
    }
}
