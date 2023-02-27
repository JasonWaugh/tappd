<?php
/**
 * Copyright © Rhubarb Tech Inc. All Rights Reserved.
 *
 * All information contained herein is, and remains the property of Rhubarb Tech Incorporated.
 * The intellectual and technical concepts contained herein are proprietary to Rhubarb Tech Incorporated and
 * are protected by trade secret or copyright law. Dissemination and modification of this information or
 * reproduction of this material is strictly forbidden unless prior written permission is obtained from
 * Rhubarb Tech Incorporated.
 *
 * You should have received a copy of the `LICENSE` with this file. If not, please visit:
 * https://objectcache.pro/license.txt
 */

declare(strict_types=1);

namespace RedisCachePro\Clients;

use Closure;
use Throwable;
use InvalidArgumentException;

use RedisCachePro\Configuration\Configuration;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\API\Common\Instrumentation\Globals;

abstract class Client implements ClientInterface
{
    /**
     * The client instance.
     *
     * @var object
     */
    protected $client;

    /**
     * The callback name.
     *
     * @var string
     */
    protected $callback;

    /**
     * The context.
     *
     * @var mixed
     */
    protected $context;

    /**
     * Creates a new `ClientInterface` instance.
     *
     * @param  callable  $client
     * @param  string|callable|null  $callback
     * @param  mixed  $context
     * @return void
     */
    public function __construct(callable $client, $callback = null, $context = null)
    {
        $this->context = $this->prepareContext($context, $callback);

        if (\is_callable($callback)) {
            $callback = 'callable';
        }

        if ($callback === Configuration::TRACER_NONE) {
            $callback = null;
        }

        if ($callback === Configuration::TRACER_OPENTELEMETRY && ! $this->context) {
            $callback = null;
        }

        $this->callback = $callback ? "{$callback}Callback" : 'passthroughCallback';

        if (! \method_exists($this, $this->callback)) {
            throw new InvalidArgumentException("Callback `{$callback}` is not supported by " . __CLASS__);
        }

        $this->client = $this->{$this->callback}($client, '__construct');
    }

    /**
     * Forwards all calls to registered callback.
     *
     * @param  string  $method
     * @param  array<mixed>  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->{$this->callback}(function () use ($method, $arguments) {
            return $this->client->{$method}(...$arguments);
        }, \strtolower($method));
    }

    /**
     * Returns prepared context before it's being set.
     *
     * @param  mixed  $context
     * @param  string|callable|null  $callback
     * @return mixed
     */
    protected function prepareContext($context, $callback)
    {
        if (\is_callable($callback)) {
            return $callback;
        }

        if (
            ! \class_exists(TracerProviderInterface::class) ||
            ! \class_exists(NoopTracer::class) ||
            ! \class_exists(Globals::class)
        ) {
            return $context;
        }

        if (! $context && $callback === Configuration::TRACER_OPENTELEMETRY) {
            $context = Globals::tracerProvider();
        }

        if ($context instanceof TracerProviderInterface) {
            $context = $this->createOpenTelemetryTracer($context);
        }

        if ($context instanceof NoopTracer) {
            return;
        }

        return $context;
    }

    /**
     * Executes given callback.
     *
     * @param  \Closure  $cb
     * @param  string  $method
     * @return mixed
     */
    protected function passthroughCallback(Closure $cb, string $method)
    {
        return $cb();
    }

    /**
     * Executes given callback on callable.
     *
     * @param  \Closure  $cb
     * @param  string  $method
     * @return mixed
     */
    protected function callableCallback(Closure $cb, string $method)
    {
        return ($this->context)($cb, $method);
    }

    /**
     * Executes given callback as New Relic datastore segment.
     *
     * @param  \Closure  $cb
     * @param  string  $method
     * @return mixed
     */
    protected function newRelicCallback(Closure $cb, string $method)
    {
        return \newrelic_record_datastore_segment($cb, [
            'product' => $this->context ?? 'Redis',
            'operation' => $method,
        ]);
    }

    /**
     * Executes given callback using Open Telemetry tracer.
     *
     * @param  \Closure  $cb
     * @param  string  $method
     * @return mixed
     */
    protected function openTelemetryCallback(Closure $cb, string $method)
    {
        $span = $this->context->spanBuilder($method)
            ->setAttribute('db.system', 'redis')
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->startSpan();

        try {
            return $cb();
        } catch (Throwable $exception) {
            $span->recordException($exception);

            throw $exception;
        } finally {
            $span->end();
        }
    }

    /**
     * Creates an OpenTelemetry tracer from given tracer provider.
     *
     * @param  TracerProviderInterface  $tracerProvider
     * @return TracerInterface
     */
    abstract protected function createOpenTelemetryTracer(TracerProviderInterface $tracerProvider): TracerInterface;
}
