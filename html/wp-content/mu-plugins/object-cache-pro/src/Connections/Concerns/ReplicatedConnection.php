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

namespace RedisCachePro\Connections\Concerns;

use Generator;
use Throwable;

use RedisCachePro\Connections\Transaction;
use RedisCachePro\Exceptions\ConnectionException;

trait ReplicatedConnection
{
    /**
     * Run a command against Redis.
     *
     * @param  string  $name
     * @param  array<mixed>  $parameters
     * @return mixed
     */
    public function command(string $name, array $parameters = [])
    {
        $isReading = \in_array(\strtoupper($name), $this->readonly);

        // send `alloptions` hash read requests to the primary
        if ($isReading && $this->config->split_alloptions && \is_string($parameters[0] ?? null)) {
            $isReading = \strpos($parameters[0], 'options:alloptions:') === false;
        }

        return $isReading
            ? $this->pool[\array_rand($this->pool)]->command($name, $parameters)
            : $this->primary->command($name, $parameters);
    }

    /**
     * Execute all `pipeline()` calls on primary node.
     *
     * @return object
     */
    public function pipeline()
    {
        return Transaction::pipeline($this->primary);
    }

    /**
     * Hijack `multi()` calls to allow command logging.
     *
     * @param  int  $type
     * @return object
     */
    public function multi(int $type = null)
    {
        $tx = parent::multi($type);
        $tx->connection = $this->primary;

        return $tx;
    }

    /**
     * Send `scan()` calls directly to random node from pool to make passed-by-reference iterator work.
     *
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<string>|false
     */
    public function scan(&$iterator, $match = null, $count = 0)
    {
        $replica = key($this->pool);

        return $this->pool[$replica]->scan($iterator, $match, $count);
    }

    /**
     * Send `hscan()` calls directly to random node from pool to make passed-by-reference iterator work.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<string>|false
     */
    public function hscan($key, &$iterator, $match = null, int $count = 0)
    {
        $replica = key($this->pool);

        return $this->pool[$replica]->hscan($key, $iterator, $match, $count);
    }

    /**
     * Send `sscan()` calls directly to random node from pool to make passed-by-reference iterator work.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<string>|false
     */
    public function sscan($key, &$iterator, $match = null, int $count = 0)
    {
        $replica = key($this->pool);

        return $this->pool[$replica]->sscan($key, $iterator, $match, $count);
    }

    /**
     * Send `zscan()` calls directly to random node from pool to make passed-by-reference iterator work.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<string>|false
     */
    public function zscan($key, &$iterator, $match = null, int $count = 0)
    {
        $replica = key($this->pool);

        return $this->pool[$replica]->zscan($key, $iterator, $match, $count);
    }

    /**
     * Yields all keys matching the given pattern.
     *
     * @param  string|null  $pattern
     * @return \Generator<array<int, mixed>>
     */
    public function listKeys(?string $pattern = null): Generator
    {
        $replica = key($this->pool);
        $iterator = null;

        do {
            $keys = $this->pool[$replica]->scan($iterator, $pattern, 500);

            if (! empty($keys)) {
                yield $keys;
            }
        } while ($iterator > 0);
    }

    /**
     * Execute the callback without data mutations on the connection,
     * such as serialization and compression algorithms.
     *
     * @param  callable  $callback
     * @return mixed
     */
    public function withoutMutations(callable $callback)
    {
        $this->primary->unsetSerializer();
        $this->primary->unsetCompression();

        foreach ($this->replicas as $replica) {
            $replica->unsetSerializer();
            $replica->unsetCompression();
        }

        try {
            return $callback($this);
        } catch (Throwable $exception) {
            throw $exception;
        } finally {
            $this->primary->setSerializer();
            $this->primary->setCompression();

            foreach ($this->replicas as $replica) {
                $replica->setSerializer();
                $replica->setCompression();
            }
        }
    }

    /**
     * Execute callback with custom read timeout.
     *
     * @param  callable  $callback
     * @param  mixed  $timeout
     * @return mixed
     */
    public function withTimeout(callable $callback, $timeout)
    {
        $this->primary->setTimeout((string) $timeout);

        foreach ($this->replicas as $replica) {
            $replica->setTimeout((string) $timeout);
        }

        try {
            return $callback($this);
        } catch (Throwable $exception) {
            throw $exception;
        } finally {
            $this->primary->setTimeout((string) $this->config->read_timeout);

            foreach ($this->replicas as $replica) {
                $replica->setTimeout((string) $this->config->read_timeout);
            }
        }
    }

    /**
     * Set the pool based on the config's `replication_strategy`.
     *
     * @return void
     */
    protected function setPool()
    {
        $strategy = $this->config->replication_strategy;

        if ($strategy === 'distribute') {
            $this->pool = array_merge([$this->primary], $this->replicas);

            return;
        }

        if (empty($this->replicas)) {
            throw new ConnectionException(
                "No replicas configured/discovered for `{$strategy}` replication strategy"
            );
        }

        if ($strategy === 'distribute_replicas') {
            $this->pool = $this->replicas;
        }

        if ($strategy === 'concentrate') {
            $this->pool = [$this->replicas[array_rand($this->replicas)]];
        }
    }
}
