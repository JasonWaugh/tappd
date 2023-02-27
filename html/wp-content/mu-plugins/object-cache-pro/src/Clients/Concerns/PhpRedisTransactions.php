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

namespace RedisCachePro\Clients\Concerns;

use LogicException;

use RedisCachePro\Clients\Transaction;

trait PhpRedisTransactions
{
    /**
     * Hijack pipeline calls to trace them.
     *
     * @return \RedisCachePro\Clients\Transaction
     */
    public function pipeline()
    {
        return new Transaction($this, self::PIPELINE);
    }

    /**
     * Hijack multi calls to trace them.
     *
     * @param  int  $mode
     * @return \RedisCachePro\Clients\Transaction
     */
    public function multi(int $mode = self::MULTI)
    {
        return new Transaction($this, $mode);
    }

    /**
     * Block non-chained transactions.
     *
     * @return void
     */
    public function exec()
    {
        throw new LogicException('Non-chained transactions are not supported');
    }

    /**
     * Executes buffered transaction using client's callback.
     *
     * @phpstan-return mixed
     *
     * @param  \RedisCachePro\Clients\Transaction  $transaction
     * @return array<int, mixed>|bool
     */
    public function executeBufferedTransaction(Transaction $transaction)
    {
        $method = $transaction->context === self::MULTI ? 'multi' : 'pipeline';

        return $this->{$this->callback}(function () use ($transaction, $method) {
            $pipe = $this->client->{$method}();

            foreach ($transaction->commands as $command) {
                $pipe->{$command[0]}(...$command[1]);
            }

            return $pipe->exec();
        }, 'exec');
    }
}
