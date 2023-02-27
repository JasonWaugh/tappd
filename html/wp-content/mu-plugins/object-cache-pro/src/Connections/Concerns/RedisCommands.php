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

trait RedisCommands
{
    /**
     * List of read-only Redis commands.
     *
     * ```
     * curl --silent "https://raw.githubusercontent.com/redis/redis-doc/master/commands.json" \
     *   | jq -r 'with_entries( select( .value.command_flags[]? | contains("readonly") ) ) | keys'
     * ```
     *
     * @var array<int, string>
     */
    protected $readonly = [
        'BITCOUNT',
        'BITFIELD_RO',
        'BITPOS',
        'DBSIZE',
        'DUMP',
        'EVALSHA_RO',
        'EVAL_RO',
        'EXISTS',
        'EXPIRETIME',
        'FCALL_RO',
        'GEODIST',
        'GEOHASH',
        'GEOPOS',
        'GEORADIUSBYMEMBER_RO',
        'GEORADIUS_RO',
        'GEOSEARCH',
        'GET',
        'GETBIT',
        'GETRANGE',
        'HEXISTS',
        'HGET',
        'HGETALL',
        'HKEYS',
        'HLEN',
        'HMGET',
        'HRANDFIELD',
        'HSCAN',
        'HSTRLEN',
        'HVALS',
        'KEYS',
        'LCS',
        'LINDEX',
        'LLEN',
        'LOLWUT',
        'LPOS',
        'LRANGE',
        'MEMORY USAGE',
        'MGET',
        'OBJECT',
        'PEXPIRETIME',
        'PFCOUNT',
        'PTTL',
        'RANDOMKEY',
        'SCAN',
        'SCARD',
        'SDIFF',
        'SINTER',
        'SINTERCARD',
        'SISMEMBER',
        'SMEMBERS',
        'SMISMEMBER',
        'SORT_RO',
        'SRANDMEMBER',
        'SSCAN',
        'STRLEN',
        'SUBSTR',
        'SUNION',
        'TOUCH',
        'TTL',
        'TYPE',
        'XINFO',
        'XLEN',
        'XPENDING',
        'XRANGE',
        'XREAD',
        'XREVRANGE',
        'ZCARD',
        'ZCOUNT',
        'ZDIFF',
        'ZINTER',
        'ZINTERCARD',
        'ZLEXCOUNT',
        'ZMSCORE',
        'ZRANDMEMBER',
        'ZRANGE',
        'ZRANGEBYLEX',
        'ZRANGEBYSCORE',
        'ZRANK',
        'ZREVRANGE',
        'ZREVRANGEBYLEX',
        'ZREVRANGEBYSCORE',
        'ZREVRANK',
        'ZSCAN',
        'ZSCORE',
        'ZUNION',
    ];
}
