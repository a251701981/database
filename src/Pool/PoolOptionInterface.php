<?php
namespace CloverSwoole\Database\Pool;
/**
 * Interface PoolOptionInterface
 * @package CloverSwoole\Database\Pool
 */
interface PoolOptionInterface
{
    public function getMaxConnections(): int;

    public function getMinConnections(): int;

    public function getConnectTimeout(): float;

    public function getWaitTimeout(): float;

    public function getHeartbeat(): float;

    public function getMaxIdleTime(): float;
}
