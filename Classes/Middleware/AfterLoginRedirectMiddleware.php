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
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Session\UserSessionManager;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class AfterLoginRedirectMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUri()->getPath() === '/sso/auth') {
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
