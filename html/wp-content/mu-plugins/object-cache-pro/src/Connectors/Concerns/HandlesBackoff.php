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

namespace RedisCachePro\Connectors\Concerns;

use RedisCachePro\Configuration\Configuration;

trait HandlesBackoff
{
    /**
     * Returns the next delay for the given retry.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @param  int  $retries
     * @return int
     */
    public static function nextDelay(Configuration $config, int $retries)
    {
        if ($config->backoff === Configuration::BACKOFF_NONE) {
            return $retries ** 2;
        }

        $retryInterval = $config->retry_interval;
        $jitter = $retryInterval * 0.1;

        return $retries * \mt_rand(
            (int) \floor($retryInterval - $jitter),
            (int) \ceil($retryInterval + $jitter)
        );
    }
}
