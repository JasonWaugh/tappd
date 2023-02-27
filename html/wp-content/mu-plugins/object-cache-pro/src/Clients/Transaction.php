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

use LogicException;

final class Transaction
{
    /**
     * The transaction's context.
     *
     * @var mixed
     */
    public $context;

    /**
     * The underlying client to execute the transaction on.
     *
     * @var \RedisCachePro\Clients\ClientInterface
     */
    public $client;

    /**
     * Holds all queued commands.
     *
     * @var array<int, array{string, mixed}>
     */
    public $commands = [];

    /**
     * Creates a new transaction instance.
     *
     * @param  \RedisCachePro\Clients\ClientInterface  $client
     * @param  mixed  $context
     * @return void
     */
    public function __construct(ClientInterface $client, $context)
    {
        $this->client = $client;
        $this->context = $context;
    }

    /**
     * Memorize all method calls for later execution.
     *
     * @param  string  $method
     * @param  array<mixed>  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $this->commands[] = [$method, $arguments];

        return $this;
    }

    /**
     * Executes the transaction on the underlying client.
     *
     * @return array<mixed>|bool
     */
    public function exec()
    {
        if (! method_exists($this->client, 'executeBufferedTransaction')) {
            throw new LogicException('Client does not implement `executeBufferedTransaction()` method');
        }

        return $this->client->executeBufferedTransaction($this);
    }

    /**
     * Block nested pipelines.
     *
     * @return void
     */
    public function pipeline()
    {
        throw new LogicException('Nested pipelines are not supported');
    }

    /**
     * Block nested MULTI transactions.
     *
     * @return void
     */
    public function multi()
    {
        throw new LogicException('Nested `MULTI` transactions are not supported');
    }
}
