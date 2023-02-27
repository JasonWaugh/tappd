<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Providers\Http\Requests;

use Exception;
use GoDaddy\WordPress\MWC\Common\Http\Contracts\ResponseContract;
use GoDaddy\WordPress\MWC\Common\Http\GoDaddyRequest;

/**
 * Abstract Request class.
 */
abstract class AbstractRequest extends GoDaddyRequest
{
    /** @var string */
    protected string $storeId = '';

    /**
     * getBaseUrl for API endpoint.
     *
     * @return string
     */
    abstract protected function getBaseUrl() : string;

    /**
     * getPrefix for API endpoint.
     *
     * @return string
     */
    protected function getPrefix() : string
    {
        return '/v1/commerce/customers/proxy/stores/'.$this->storeId;
    }

    /**
     * Sends the request.
     *
     * @return ResponseContract
     * @throws Exception
     */
    public function send() : ResponseContract
    {
        if (empty($this->url)) {
            $this->setUrl($this->getBaseUrl().$this->getPrefix());
        }

        return parent::send();
    }

    /**
     * Gets storeId for the Request.
     *
     * @return string
     */
    public function getStoreId() : string
    {
        return $this->storeId;
    }

    /**
     * Sets the request's storeId.
     *
     * @param string $value
     *
     * @return $this
     */
    public function setStoreId(string $value) : AbstractRequest
    {
        $this->storeId = $value;

        return $this;
    }
}
