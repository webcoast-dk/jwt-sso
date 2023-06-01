<?php

declare(strict_types=1);

namespace WEBcoast\JwtSso\Api;

use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use WEBcoast\JwtSso\Exception\ApiResponseException;

class Client
{
    protected string $baseUrl;

    protected string $accessToken;

    protected ?string $basicAuthUser;

    protected ?string $basicAuthPassword;

    public function __construct()
    {
        $extensionConfig = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jwt_sso'];
        $this->baseUrl = $extensionConfig['api']['base_url'];
        $this->accessToken = $extensionConfig['api']['access_token'];
        $this->basicAuthUser = $extensionConfig['api']['basic_auth']['user'] ?? null;
        $this->basicAuthPassword = $extensionConfig['api']['basic_auth']['password'] ?? null;
    }

    /**
     * @param string $path
     * @param array  $parameters
     *
     * @throws ApiResponseException
     * @return array
     */
    protected function get(string $path, array $parameters = [])
    {
        $uri = (new Uri($this->baseUrl . $path))
            ->withQuery(HttpUtility::buildQueryString($parameters));
        $options = [
            'headers' => [
                'X-Auth-Token' => 'Bearer ' . $this->accessToken,
            ],
        ];

        if (!empty($this->basicAuthUser) && !empty($this->basicAuthPassword)) {
            $options['auth'] = [$this->basicAuthUser, $this->basicAuthPassword];
        }
        $response = GeneralUtility::makeInstance(RequestFactory::class)->request((string) $uri, 'GET', $options);

        if ($response->getStatusCode() === 200) {
            return json_decode($response->getBody()->getContents(), true);
        }

        throw new ApiResponseException(sprintf('Unexpected response code %s', $response->getStatusCode()), 1661415147);
    }

    /**
     * @param int $id
     *
     * @throws ApiResponseException
     *
     * @return array
     */
    public function getUser(int $id)
    {
        return $this->get('/user/' . $id);
    }
}
