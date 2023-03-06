<?php

declare(strict_types=1);

namespace WEBcoast\JwtSso\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Session\UserSessionManager;

class SsoLogoutMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $language = $request->getAttribute('language');
        if ($language) {
            $uri = new Uri(rtrim((string) $language->getBase(), '/') . '/' . ltrim($language->toArray()['jwtsso_logout_route'], '/'));
            if ($request->getUri()->getPath() === $uri->getPath()) {
                $sessionManager = UserSessionManager::create('FE');
                $session = $sessionManager->createFromRequestOrAnonymous($request, $GLOBALS['TYPO3_CONF_VARS']['FE']['cookieName']);
                $sessionManager->removeSession($session);
                $referrer = $request->getHeader('Referer')[0];
                $site = $request->getAttribute('site');

                return new RedirectResponse($referrer ?? $site->getBase());
            }
        }

        return $handler->handle($request);
    }
}
