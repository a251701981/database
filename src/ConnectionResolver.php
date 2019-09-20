<?php

namespace CloverSwoole\Database;

use CloverSwoole\Database\Pool\PoolFactory;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * 链接解析器
 * Class ConnectionResolver
 * @package CloverSwoole\Database
 */
class ConnectionResolver extends \Illuminate\Database\ConnectionResolver
{
    /**
     * 连接池工厂
     * @var PoolFactory
     */
    protected $factory;
    /**
     * 容器
     * @var Container
     */
    protected $container;
    /**
     * The default connection name.
     *
     * @var string
     */
    protected $default = 'default';

    /**
     * ConnectionResolver constructor.
     * @param Container $container
     * @throws BindingResolutionException
     */
    public function __construct(Container $container)
    {
        /**
         * 容器
         */
        $this->container = $container;
        /**
         * 创建连接池工厂
         */
        $this->factory = $container->make(PoolFactory::class, ['container' => $container]);
    }

    /**
     * 获取连接
     * @param null $name
     * @return Connection|ConnectionInterface|mixed|null
     * @throws BindingResolutionException
     * @throws \Throwable
     */
    public function connection($name = null)
    {
        /**
         * 连接名称
         */
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }
        /**
         * 获取连接key
         */
        $key = $this->getContextKey($name);
        /**
         * 判断上下文管理器是否存在链接
         */
        if (ContextManager::has($key)) {
            $connection = ContextManager::get($key);
        } else {
            $connection = null;
        }
        /**
         * 判断链接是否已经建立
         */
        if (!$connection instanceof ConnectionInterface) {
            /**
             * 创建连接池
             */
            $pool = $this->factory->getPool(strval($name));
            /**
             * 获取连接
             * @var $connection Connection
             */
            $connection = $pool->get()->getConnection();
            /**
             * 连接存放到上下文管理器
             */
            ContextManager::set($key, $connection);
            /**
             * 判断是否在协程内
             */
            if (Coroutine::inCoroutine()) {
                /**
                 * 协程结束归还释放链接
                 */
                defer(function () use ($connection) {
                    $connection->release();
                });
            }
        }
        /**
         * 返回链接
         */
        return $connection;
    }

    /**
     * 获取上下文的key
     * @param $name
     * @return string
     */
    private static function getContextKey($name): string
    {
        return sprintf('database.connection.%s', $name);
    }
}