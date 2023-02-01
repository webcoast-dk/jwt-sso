<?php

declare(strict_types=1);

namespace WEBcoast\JwtSso\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AfterLoginRedirectMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $language = $request->getAttribute('language');
        $uri = new Uri(rtrim((string) $language->getBase(), '/') . SsoCallbackMiddleware::SSO_AUTH_URL);
        if ($request->getUri()->getPath() === $uri->getPath()) {
            $context = GeneralUtility::makeInstance(Context::class);
            if ($userAspect = $context->getAspect('frontend.user')) {
                /** @var $userAspect UserAspect */
                if ($userAspect->isLoggedIn()) {
                    if (!empty($redirectUrl = $request->getQueryParams()['redirect_url'] ?? null)) {
                        return new RedirectResponse($redirectUrl);
                    }
                    return new RedirectResponse($request->getAttribute('site')->getBase());
                }
            }
        }

        return $handler->handle($request);
    }
}
