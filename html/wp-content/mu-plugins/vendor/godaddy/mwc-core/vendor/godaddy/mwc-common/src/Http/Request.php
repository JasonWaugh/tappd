<?php

namespace GoDaddy\WordPress\MWC\Common\Http;

use Exception;
use GoDaddy\WordPress\MWC\Common\Auth\Contracts\AuthMethodContract;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Http\Contracts\RequestContract;
use GoDaddy\WordPress\MWC\Common\Http\Contracts\ResponseContract;
use GoDaddy\WordPress\MWC\Common\Http\Url\Exceptions\InvalidUrlException;
use GoDaddy\WordPress\MWC\Common\Repositories\ManagedWooCommerceRepository;
use GoDaddy\WordPress\MWC\Common\Repositories\WordPress\HttpRepository;

/**
 * HTTP Request handler.
 */
class Request implements RequestContract
{
    /** @var array<mixed> request body */
    public $body;

    /** @var array<string, mixed> request headers */
    public $headers;

    /** @var string request method */
    public $method;

    /** @var array<mixed>|null request query parameters */
    public $query;

    /** @var bool whether should verify SSL */
    public $sslVerify;

    /** @var int default timeout in seconds */
    public $timeout;

    /** @var string|null the URL to send the request to */
    public $url;

    /** @var string[] allowed request method types */
    protected $allowedMethodTypes = ['GET', 'POST', 'HEAD', 'PUT', 'DELETE', 'TRACE', 'OPTIONS', 'PATCH'];

    /** @var string default allowed method */
    protected $defaultAllowedMethod = 'get';

    /** @var class-string<ResponseContract> the type of response the request should return */
    protected $responseClass = Response::class;

    /** @var AuthMethodContract|null The authentication method for this request. */
    protected $authMethod;

    /** @var string */
    protected $path;

    /**
     * Request constructor.
     *
     * @param string|null $url
     * @throws Exception
     */
    public function __construct(string $url = null)
    {
        $this->setHeaders()
             ->setMethod()
             ->sslVerify()
             ->setTimeout();

        if ($url) {
            $this->setUrl($url);
        }
    }

    /**
     * @deprecated Please use setBody()
     *
     * @param array<mixed> $body
     * @return $this
     */
    public function body(array $body) : Request
    {
        return $this->setBody($body);
    }

    /**
     * {@inheritDoc}
     */
    public function setAuthMethod(AuthMethodContract $value)
    {
        $this->authMethod = $value;
        $this->authMethod->prepare($this);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthMethod() : ?AuthMethodContract
    {
        return $this->authMethod;
    }

    /**
     * {@inheritDoc}
     */
    public function buildUrlString() : string
    {
        $url = $this->url;

        if ($this->path) {
            $url = trim($url ?: '', '/').$this->path;
        }

        $queryString = ! empty($this->query) ? '?'.ArrayHelper::query($this->query) : '';

        return $url.$queryString;
    }

    /**
     * Sets Request headers.
     *
     * @param array<string, mixed>|null $additionalHeaders
     * @return $this
     * @throws Exception
     */
    public function headers($additionalHeaders = []) : Request
    {
        return $this->setHeaders($additionalHeaders);
    }

    /**
     * Sets the request method.
     *
     * @param string|null $method
     * @return $this
     */
    public function setMethod(string $method = null) : RequestContract
    {
        if (! $method || ! ArrayHelper::contains($this->allowedMethodTypes, strtoupper($method))) {
            $method = $this->defaultAllowedMethod ?? 'get';
        }

        $this->method = strtoupper($method);

        return $this;
    }

    /**
     * @deprecated use setQuery()
     *
     * @param array<mixed> $params
     * @return $this
     */
    public function query(array $params) : Request
    {
        return $this->setQuery($params);
    }

    /**
     * Sends the request.
     *
     * @return ResponseContract
     * @throws Exception
     */
    public function send()
    {
        $this->validate();

        return new $this->responseClass(HttpRepository::performRequest(
            $this->buildUrlString(),
            [
                'body'      => $this->body ? json_encode($this->body) : null,
                'headers'   => $this->headers,
                'method'    => $this->method,
                'sslverify' => $this->sslVerify,
                'timeout'   => $this->timeout,
            ]
        ));
    }

    /**
     * Sets the body of the request.
     *
     * @param array<mixed> $body
     * @return $this
     */
    public function setBody(array $body) : RequestContract
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Sets Request headers.
     *
     * @param array<string, mixed>|null $additionalHeaders
     * @return $this
     * @throws Exception
     */
    public function setHeaders($additionalHeaders = []) : RequestContract
    {
        $this->headers = ArrayHelper::combine([
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ], $additionalHeaders);

        return $this;
    }

    /**
     * Merges the provided Request headers with the headers already set.
     *
     * @param array<string, mixed> $additionalHeaders
     * @return $this
     * @throws Exception
     */
    public function addHeaders(array $additionalHeaders) : RequestContract
    {
        $this->headers = ArrayHelper::combine($this->headers, $additionalHeaders);

        return $this;
    }

    /**
     * Sets query parameters.
     *
     * @param array<mixed> $params
     * @return $this
     */
    public function setQuery(array $params) : RequestContract
    {
        $this->query = $params;

        return $this;
    }

    /**
     * Sets the request timeout.
     *
     * @param int $seconds
     * @return $this
     */
    public function setTimeout(int $seconds = 3) : RequestContract
    {
        $this->timeout = $seconds;

        return $this;
    }

    /**
     * Sets the url of the request.
     *
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url) : RequestContract
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Gets the request path.
     *
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * Sets the request path.
     *
     * @param string $value
     * @return $this
     */
    public function setPath(string $value) : RequestContract
    {
        $this->path = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function sslVerify(bool $default = false) : RequestContract
    {
        $this->sslVerify = $default || ManagedWooCommerceRepository::isProductionEnvironment();

        return $this;
    }

    /**
     * @deprecated use setTimeout()
     */
    public function timeout(int $seconds = 3) : Request
    {
        return $this->setTimeout($seconds);
    }

    /**
     * @deprecated use setUrl()
     */
    public function url(string $url) : Request
    {
        return $this->setUrl($url);
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     * @throws InvalidUrlException
     */
    public function validate() : void
    {
        if (! $this->url) {
            throw new InvalidUrlException('You must provide a url for an outgoing request');
        }
    }
}
