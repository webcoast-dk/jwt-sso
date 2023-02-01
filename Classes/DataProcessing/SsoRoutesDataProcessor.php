<?php

declare(strict_types=1);

namespace WEBcoast\JwtSso\DataProcessing;

use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class SsoRoutesDataProcessor implements DataProcessorInterface
{
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {
        /** @var ServerRequest $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        /** @var SiteLanguage $siteLanguage */
        $siteLanguage = $request->getAttribute('language');

        $languageBase = $siteLanguage->getBase();

        $processedData['jwt_sso'] = [
            'loginUri' => rtrim((string) $languageBase, '/') . '/' . ltrim($siteLanguage->toArray()['jwtsso_login_route'], '/'),
            'logoutUri' => rtrim((string) $languageBase, '/') . '/' . ltrim($siteLanguage->toArray()['jwtsso_logout_route'], '/'),
        ];

        return $processedData;
    }
}
