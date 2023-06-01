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

                $extensionConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('jwt_sso');
                if ((int) $extensionConfig['enableSsoLogout'] ?? null) {
                    $config = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jwt_sso'];
                    $clientPrivateKey = JWKFactory::createFromKeyFile($config['client_private_key']);

                    $builder = new JWSBuilder(new AlgorithmManager([new RS256()]));
                    $jws = $builder->create()
                        ->withPayload(json_encode(['iss' => $config['client_identifier'], 'action' => 'logout', 'redirect_url' => ($referrer ?? (string) $language->getBase())]))
                        ->addSignature($clientPrivateKey, ['alg' => 'RS256'])
                        ->build();

                    $token = (new CompactSerializer())->serialize($jws, 0);

                    return new RedirectResponse(sprintf($config['server_logout_entry_point'], $token));
                } else {
                    return new RedirectResponse($referrer ?? $language->getBase());
                }
            }
        }

        return $handler->handle($request);
    }
}
