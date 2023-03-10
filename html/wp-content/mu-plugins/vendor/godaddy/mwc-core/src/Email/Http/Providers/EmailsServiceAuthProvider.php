<?php

namespace GoDaddy\WordPress\MWC\Core\Email\Http\Providers;

use Exception;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\DeprecationHelper;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Auth\Providers\EmailsService\JwtAuthProvider;
use GoDaddy\WordPress\MWC\Core\Email\Cache\Types\CacheEmailsServiceToken;
use GoDaddy\WordPress\MWC\Core\Email\Exceptions\EmailsServiceAuthProviderException;
use GoDaddy\WordPress\MWC\Core\Email\Http\EmailsServiceRequest;
use GoDaddy\WordPress\MWC\Core\Email\Http\GraphQL\Mutations\IssueTokenForSiteMutation;
use GoDaddy\WordPress\MWC\Core\Email\Repositories\EmailServiceRepository;

/**
 * @deprecated use \GoDaddy\WordPress\MWC\Core\Auth\Providers\EmailsService\AuthProvider instead
 */
class EmailsServiceAuthProvider
{
    use CanGetNewInstanceTrait;

    /**
     * Attempts to retrieve cached token. Otherwise, will request a new token.
     *
     * @deprecated
     *
     * @return string
     * @throws EmailsServiceAuthProviderException
     */
    public function get() : string
    {
        DeprecationHelper::deprecatedFunction(__FUNCTION__, '3.2.2', JwtAuthProvider::class);

        return CacheEmailsServiceToken::getInstance()->get() ?? $this->requestToken();
    }

    /**
     * Clears the cached token.
     *
     * @deprecated
     *
     * @return EmailsServiceAuthProvider self
     */
    public function forget() : EmailsServiceAuthProvider
    {
        DeprecationHelper::deprecatedFunction(__FUNCTION__, '3.2.2', JwtAuthProvider::class);

        CacheEmailsServiceToken::getInstance()->clear();

        return $this;
    }

    /**
     * Requests a new token from the Emails Service.
     *
     * @return string
     * @throws EmailsServiceAuthProviderException
     * @throws Exception
     */
    protected function requestToken() : string
    {
        $response = $this->getEmailsServiceRequest()->send();

        if ($response->isError() || empty($token = ArrayHelper::get($response->getBody(), 'data.issueTokenForSite'))) {
            throw new EmailsServiceAuthProviderException("API responded with error: {$response->getErrorMessage()}");
        }

        CacheEmailsServiceToken::getInstance()->set($token);

        return $token;
    }

    /**
     * Gets a proper Emails Service request instance to issue site token.
     *
     * @return EmailsServiceRequest
     * @throws Exception
     */
    protected function getEmailsServiceRequest() : EmailsServiceRequest
    {
        return (new EmailsServiceRequest())->setOperation($this->getIssueTokenForSiteMutation());
    }

    /**
     * Gets an issue site token GraphQL mutation operation.
     *
     * @return IssueTokenForSiteMutation
     * @throws Exception
     */
    protected function getIssueTokenForSiteMutation() : IssueTokenForSiteMutation
    {
        return (new IssueTokenForSiteMutation())
            ->setVariables([
                'siteId'    => EmailServiceRepository::getSiteId(),
                'uid'       => Configuration::get('godaddy.account.uid'),
                'siteToken' => Configuration::get('godaddy.site.token', 'empty'),
            ]);
    }
}
