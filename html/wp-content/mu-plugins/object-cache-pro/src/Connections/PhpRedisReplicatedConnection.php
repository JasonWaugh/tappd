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

namespace RedisCachePro\Connections;

use Generator;
use Throwable;

use RedisCachePro\Configuration\Configuration;
use RedisCachePro\Connectors\PhpRedisConnector;
use RedisCachePro\Exceptions\ConnectionException;

class PhpRedisReplicatedConnection extends PhpRedisConnection implements ConnectionInterface
{
    use Concerns\RedisCommands,
        Concerns\ReplicatedConnection;

    /**
     * The primary connection.
     *
     * @var \RedisCachePro\Connections\PhpRedisConnection
     */
    protected $primary;

    /**
     * An array of replica connections.
     *
     * @var \RedisCachePro\Connections\PhpRedisConnection[]
     */
    protected $replicas;

    /**
     * The pool of connections for read commands.
     *
     * @var \RedisCachePro\Connections\PhpRedisConnection[]
     */
    protected $pool;

    /**
     * Create a new replicated PhpRedis connection.
     *
     * @param  \RedisCachePro\Connections\PhpRedisConnection  $primary
     * @param  \RedisCachePro\Connections\PhpRedisConnection[]  $replicas
     * @param  \RedisCachePro\Configuration\Configuration  $config
     */
    public function __construct(PhpRedisConnection $primary, array $replicas, Configuration $config)
    {
        $this->primary = $primary;
        $this->replicas = $replicas;
        $this->config = $config;

        $this->log = $this->config->logger;

        if (empty($this->replicas)) {
            $this->discoverReplicas();
        }

        $this->setPool();
    }

    /**
     * Discovers and connects to the replicas from the primary's configuration.
     *
     * @return void
     */
    protected function discoverReplicas()
    {
        $info = $this->primary->info('replication');

        if (! is_array($info)) {
            throw new ConnectionException('Unable to discover replicas');
        }

        if (! in_array($info['role'], ['primary', 'master'])) {
            throw new ConnectionException("Replicated primary is a {$info['role']}");
        }

        foreach ($info as $key => $value) {
            if (strpos((string) $key, 'slave') !== 0) {
                continue;
            }

            $replica = null;

            if (preg_match('/ip=(?P<host>.*),port=(?P<port>\d+)/', $value, $replica)) {
                $config = clone $this->config;
                $config->setHost($replica['host']);
                $config->setPort((int) $replica['port']);

                $this->replicas[] = PhpRedisConnector::connectToInstance($config);
            }
        }
    }

    /**
     * Returns the primary's node information.
     *
     * @return \RedisCachePro\Connections\PhpRedisConnection
     */
    public function primary()
    {
        return $this->primary;
    }

    /**
     * Returns the primary's node information.
     *
     * @deprecated  1.17.0  Use `PhpRedisReplicatedConnection::primary()` instead
     *
     * @return \RedisCachePro\Connections\PhpRedisConnection
     */
    public function master()
    {
        return $this->primary;
    }

    /**
     * Returns the replica nodes information.
     *
     * @return \RedisCachePro\Connections\PhpRedisConnection[]
     */
    public function replicas()
    {
        return $this->replicas;
    }

    /**
     * Flush the selected Redis database.
     *
     * Set the connections client to the primary node and calls `PhpRedisConnection::flushdb()`.
     *
     * @param  bool|null  $async
     * @return bool
     */
    public function flushdb($async = null)
    {
        /** @var \RedisCachePro\Clients\PhpRedis $client */
        $client = $this->primary->client();

        $this->client = $client;

        return parent::flushdb($async);
    }
}
