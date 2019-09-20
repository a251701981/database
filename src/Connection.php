<?php
namespace CloverSwoole\Database;
use Illuminate\Container\Container;
use CloverSwoole\Database\Pool\DbPool;
use Illuminate\Contracts\Container\BindingResolutionException;
use Exception;

/**
 * Class Connection
 * @package CloverSwoole\Database
 */
class Connection extends BaseConnection implements ConnectionInterface
{
    use DbConnection;

    /**
     * @var DbPool
     */
    protected $pool;

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * @var ConnectionFactory
     */
    protected $factory;

    /**
     * @var array
     */
    protected $config;

    protected $transaction = false;

    /**
     * 构造器
     * Connection constructor.
     * @param Container $container
     * @param DbPool $pool
     * @param array $config
     * @throws BindingResolutionException
     */
    public function __construct(Container $container, DbPool $pool, array $config)
    {
        parent::__construct($container, $pool);
        /**
         * 创建连接工厂
         */
        $this->factory = $container->make(ConnectionFactory::class,['container'=>$container]);
        /**
         * 获取配置
         */
        $this->config = $config;
        /**
         * 连接数据库
         */
        $this->reconnect();
    }

    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function getActiveConnection()
    {
        if ($this->check()) {
            return $this;
        }

        if (! $this->reconnect()) {
            throw new Exception('Connection reconnect failed.');
        }

        return $this;
    }

    /**
     * 重连
     * @return bool
     */
    public function reconnect(): bool
    {
        /**
         * 链接工厂实例创建一个连接
         */
        $this->connection = $this->factory->make($this->config);
        /**
         * 判断链接是否有效
         */
        if ($this->connection instanceof Connection) {
            // Reset event dispatcher after db reconnect.
//            if ($this->container->has(EventDispatcherInterface::class)) {
//                $dispatcher = $this->container->get(EventDispatcherInterface::class);
//                $this->connection->setEventDispatcher($dispatcher);
//            }

            // Reset reconnector after db reconnect.
            $this->connection->setReconnector(function ($connection) {
//                $this->logger->warning('Database connection refreshing.');
                echo ('Database connection refreshing.'."\n");
                if ($connection instanceof Connection) {
                    $this->refresh($connection);
                }
            });
        }

        $this->lastUseTime = microtime(true);
        return true;
    }

    public function close(): bool
    {
        unset($this->connection);

        return true;
    }

    public function release(): void
    {
        if ($this->isTransaction()) {
            $this->rollBack(0);
//            $this->logger->error('Maybe you\'ve forgotten to commit or rollback the MySQL transaction.');
            echo('Maybe you\'ve forgotten to commit or rollback the MySQL transaction.'."\n");
        }
        parent::release();
    }

    public function setTransaction(bool $transaction): void
    {
        $this->transaction = $transaction;
    }

    public function isTransaction(): bool
    {
        return $this->transaction;
    }

    /**
     * Refresh pdo and readPdo for current connection.
     */
    protected function refresh(Connection $connection)
    {
        $refresh = $this->factory->make($this->config);
        if ($refresh instanceof \Hyperf\Database\Connection) {
            $connection->disconnect();
            $connection->setPdo($refresh->getPdo());
            $connection->setReadPdo($refresh->getReadPdo());
        }

//        $this->logger->warning('Database connection refreshed.');
        echo('Database connection refreshed.'."\n");
    }
}
