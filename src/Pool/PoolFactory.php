<?php

namespace CloverSwoole\Database\Pool;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class PoolFactory
 * @package CloverSwoole\Database\Pool
 */
class PoolFactory
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var DbPool[]
     */
    protected $pools = [];

    /**
     * PoolFactory constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * 获取连接池
     * @param string $name
     * @return DbPool
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function getPool(string $name): DbPool
    {
        /**
         * 判断连接池是否已经创建
         */
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }
        /**
         * 判断容器是否有效
         */
        if (!($this->container instanceof Container)) {
            throw new Exception('Container invalid');
        }
        /**
         * 创建连接池
         */
        $this->pools[$name] = $this->container->make(DbPool::class, ['container' => $this->container, 'name' => $name]);
        /**
         * 返回连接池
         */
        return $this->pools[$name];
    }
}
