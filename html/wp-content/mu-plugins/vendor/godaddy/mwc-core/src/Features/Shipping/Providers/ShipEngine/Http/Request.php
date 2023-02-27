<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Http;

use Exception;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Http\Contracts\ResponseContract;
use GoDaddy\WordPress\MWC\Common\Http\GoDaddyRequest;

class Request extends GoDaddyRequest
{
    /** @var class-string<ResponseContract> the type of response the request should return */
    protected $responseClass = Response::class;

    /**
     * Sends the request.
     *
     * @return Response
     * @throws Exception
     */
    public function send()
    {
        if (empty($this->url)) {
            $this->setUrl(Configuration::get('shipping.shipengine.api.url'));
        }

        /** @var Response $response */
        $response = parent::send();

        return $response;
    }
}
