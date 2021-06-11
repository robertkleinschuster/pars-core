<?php


namespace Pars\Core\Cache;


interface ParsCacheInterface
{
    public const TTL_1_WEEK = 604800;
    public const TTL_1_DAY = self::TTL_1_WEEK / 7;
    public const TTL_1_HOUR = self::TTL_1_DAY / 24;
    public const TTL_30_MIN = self::TTL_1_HOUR / 2;
    public const TTL_5_MIN = self::TTL_30_MIN / 6;
    public const TTL_1_MIN = self::TTL_5_MIN / 5;

}
