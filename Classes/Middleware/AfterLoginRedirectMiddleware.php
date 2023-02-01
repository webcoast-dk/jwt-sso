<?php

declare(strict_types=1);

namespace WEBcoast\JwtSso\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Http\Uri;

class AfterLoginRedirectMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $language = $request->getAttribute('language');
        $uri = new Uri(rtrim((string) $language->getBase(), '/') . SsoCallbackMiddleware::SSO_AUTH_URL);
        if ($request->getUri()->getPath() === $uri->getPath()) {
            if (!empty($redirectUrl = $request->getQueryParams()['redirect_url'] ?? null)) {
                return new RedirectResponse($redirectUrl);
            }
            return new RedirectResponse($request->getAttribute('site')->getBase());
        }

        return $handler->handle($request);
    }
}
