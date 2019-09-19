<?php
namespace CloverSwoole\Database;

use Illuminate\Container\Container;
use CloverSwoole\Database\Pool\Pool;
use Psr\Container\ContainerInterface;

abstract class BaseConnection implements ConnectionInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var float
     */
    protected $lastUseTime = 0.0;

    public function __construct(Container $container, Pool $pool)
    {
        $this->container = $container;
        $this->pool = $pool;
    }

    public function release(): void
    {
        $this->pool->release($this);
    }

    public function getConnection()
    {
        return $this->getActiveConnection();
    }

    public function check(): bool
    {
        $maxIdleTime = $this->pool->getOption()->getMaxIdleTime();
        $now = microtime(true);
        if ($now > $maxIdleTime + $this->lastUseTime) {
            return false;
        }

        $this->lastUseTime = $now;
        return true;
    }

    abstract public function getActiveConnection();
}
