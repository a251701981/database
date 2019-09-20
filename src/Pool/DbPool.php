<?php

namespace CloverSwoole\Database\Pool;

use CloverSwoole\Database\ConfigInterface;
use CloverSwoole\Database\Connection;
use CloverSwoole\Database\ConnectionInterface;
use CloverSwoole\Database\Frequency;
use CloverSwoole\Database\Utils\Arr;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use InvalidArgumentException;

/**
 * DB 连接池
 * Class DbPool
 * @package CloverSwoole\Database\Pool
 */
class DbPool extends Pool
{
    /**
     * 链接名称
     * @var string
     */
    protected $name;
    /**
     * 连接配置
     * @var array
     */
    protected $config;

    /**
     * DbPool constructor.
     * @param Container $container
     * @param string $name
     * @throws BindingResolutionException
     */
    public function __construct(Container $container, string $name)
    {
        /**
         * 获取name
         */
        $this->name = $name;
        /**
         * 创建配置
         */
        $config = $container->make(ConfigInterface::class);
        /**
         * 拼接出连接的key
         */
        $key = sprintf('databases.%s', $this->name);
        /**
         * 判断连接的配置是否存在
         */
        if (!$config->has($key)) {
            throw new InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }
        /**
         * 重写配置内的名称
         */
        $config->set("{$key}.name", $name);
        /**
         * 获取连接配置
         */
        $this->config = $config->get($key);
        /**
         * 获取连接池选项
         */
        $options = Arr::get($this->config, 'pool', []);
        /**
         * 实例化XXX
         */
        $this->frequency = $container->make(Frequency::class);
        /**
         * 实例池
         */
        parent::__construct($container, $options);
    }

    /**
     * 创建连接
     * @return ConnectionInterface
     * @throws BindingResolutionException
     */
    protected function createConnection(): ConnectionInterface
    {
        return new Connection($this->container, $this, $this->config);
    }
}
