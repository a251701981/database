<?php

namespace CloverSwoole\Database\Pool;

use CloverSwoole\Database\ConnectionInterface;
use CloverSwoole\Database\Frequency;
use Illuminate\Container\Container;
use RuntimeException;
use Swoole\Coroutine\Channel;
use Throwable;

abstract class Pool implements PoolInterface
{
    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var PoolOptionInterface
     */
    protected $option;

    /**
     * 当前连接数
     * @var int
     */
    protected $currentConnections = 0;

    /**
     * @var Frequency
     */
    protected $frequency;

    /**
     * Pool constructor.
     * @param Container $container
     * @param array $config
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(Container $container, array $config = [])
    {
        /**
         * 容器
         */
        $this->container = $container;
        /**
         * 初始化选项
         */
        $this->initOption($config);
        /**
         * 创建渠道
         */
        $this->channel = $container->make(Channel::class, ['size' => $this->option->getMaxConnections()]);
    }

    /**
     * 获取一个连接
     * @return ConnectionInterface
     * @throws Throwable
     */
    public function get(): ConnectionInterface
    {
        /**
         * 获取连接
         */
        $connection = $this->getConnection();
        if ($this->frequency instanceof Frequency) {
            $this->frequency->hit();
        }
        if ($this->frequency instanceof Frequency) {
            if ($this->frequency->isLowFrequency()) {
                $this->flush();
            }
        }
        return $connection;
    }

    /**
     * 释放归还连接
     * @param ConnectionInterface $connection
     */
    public function release(ConnectionInterface $connection): void
    {
        $this->channel->push($connection);
    }

    /**
     *
     */
    public function flush(): void
    {
        $num = $this->getConnectionsInChannel();

        if ($num > 0) {
            while ($this->currentConnections > $this->option->getMinConnections() && $conn = $this->channel->pop($this->option->getWaitTimeout())) {
                $conn->close();
                --$this->currentConnections;
            }
        }
    }

    /**
     * @return int
     */
    public function getCurrentConnections(): int
    {
        return $this->currentConnections;
    }

    /**
     * @return PoolOptionInterface
     */
    public function getOption(): PoolOptionInterface
    {
        return $this->option;
    }

    /**
     * @return int
     */
    protected function getConnectionsInChannel(): int
    {
        return $this->channel->length();
    }

    /**
     * 初始化选项
     * @param array $options
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function initOption(array $options = []): void
    {
        $this->option = $this->container->make(PoolOption::class, [
            'minConnections' => $options['min_connections'] ?? 1,
            'maxConnections' => $options['max_connections'] ?? 10,
            'connectTimeout' => $options['connect_timeout'] ?? 10.0,
            'waitTimeout' => $options['wait_timeout'] ?? 3.0,
            'heartbeat' => $options['heartbeat'] ?? -1,
            'maxIdleTime' => $options['max_idle_time'] ?? 60.0,
        ]);
    }

    /**
     * 创建连接(抽象)
     * @return ConnectionInterface
     */
    abstract protected function createConnection(): ConnectionInterface;

    /**
     * 获取连接
     * @return ConnectionInterface
     * @throws Throwable
     */
    private function getConnection(): ConnectionInterface
    {
        $num = $this->getConnectionsInChannel();

        try {
            /**
             * 渠道数量 === 0 并且 当前连接数 小于 最大连接数量  则开始创建连接
             */
            if ($num === 0 && $this->currentConnections < $this->option->getMaxConnections()) {
                ++$this->currentConnections;
                return $this->createConnection();
            }
        } catch (Throwable $throwable) {
            /**
             * 异常
             */
            --$this->currentConnections;
            throw $throwable;
        }
        /**
         * 出列一个连接
         */
        $connection = $this->channel->pop($this->option->getWaitTimeout());
        /**
         * 判断链接是否有效
         */
        if (!$connection instanceof ConnectionInterface) {
            throw new RuntimeException('Cannot pop the connection, pop timeout.');
        }
        return $connection;
    }
}
