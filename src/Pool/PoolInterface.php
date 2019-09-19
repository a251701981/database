<?php
namespace CloverSwoole\Database\Pool;

use \CloverSwoole\Database\ConnectionInterface;

/**
 * Interface PoolInterface
 * @package CloverSwoole\Database\Pool
 */
interface PoolInterface
{
    /**
     * Get a connection from the connection pool.
     */
    public function get(): ConnectionInterface;

    /**
     * Release a connection back to the connection pool.
     */
    public function release(ConnectionInterface $connection): void;

    /**
     * Close and clear the connection pool.
     */
    public function flush(): void;
}
