<?php

namespace GoDaddy\WordPress\MWC\Common\Http\Contracts;

use Exception;
use GoDaddy\WordPress\MWC\Common\Auth\Contracts\AuthMethodContract;

/**
 * Contract for HTTP Requests.
 */
interface RequestContract
{
    /**
     * Builds a valid url string with parameters.
     *
     * @return string
     * @throws Exception
     */
    public function buildUrlString() : string;

    /**
     * Sets the request method.
     *
     * @param string|null $method
     *
     * @return $this
     */
    public function setMethod(string $method = null) : RequestContract;

    /**
     * Sends the request.
     *
     * @return ResponseContract
     * @throws Exception
     */
    public function send();

    /**
     * Sets the body of the request.
     *
     * @param array $body
     *
     * @return RequestContract
     */
    public function setBody(array $body) : RequestContract;

    /**
     * Sets Request headers.
     *
     * @param array|null $additionalHeaders
     *
     * @return RequestContract
     */
    public function setHeaders($additionalHeaders = []) : RequestContract;

    /**
     * Merges the provided Request headers with the headers already set.
     *
     * @param array $additionalHeaders
     *
     * @return RequestContract
     */
    public function addHeaders(array $additionalHeaders) : RequestContract;

    /**
     * Sets query parameters.
     *
     * @param array $params
     *
     * @return RequestContract
     */
    public function setQuery(array $params) : RequestContract;

    /**
     * Sets the request timeout.
     *
     * @param int $seconds
     *
     * @return RequestContract
     */
    public function setTimeout(int $seconds = 30) : RequestContract;

    /**
     * Sets the url of the request.
     *
     * @param string $url
     *
     * @return RequestContract
     */
    public function setUrl(string $url) : RequestContract;

    /**
     * Sets SSL verify.
     *
     * @param bool $default
     *
     * @return $this
     */
    public function sslVerify(bool $default = false) : self;

    /**
     * Sets the auth method for this request.
     *
     * @param AuthMethodContract $value The auth method to set.
     *
     * @return $this
     */
    public function setAuthMethod(AuthMethodContract $value);

    /**
     * Gets the authentication method for this request.
     *
     * @return AuthMethodContract|null Auth method instance if it is set, otherwise null.
     */
    public function getAuthMethod() : ?AuthMethodContract;

    /**
     * Sets the request path.
     *
     * @param string $value
     *
     * @return RequestContract
     */
    public function setPath(string $value) : RequestContract;

    /**
     * Gets the request path.
     *
     * @return string|null
     */
    public function getPath() : ?string;

    /**
     * Validates the request.
     *
     * @throws Exception
     */
    public function validate();
}
