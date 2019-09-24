<?php
namespace CloverSwoole\Database;

use Illuminate\Container\Container;
use CloverSwoole\Database\QueryBuilder;
use Illuminate\Database\Query\Expression;
use Generator;

/**
 * DB Helper.
 * @method static QueryBuilder table(string $table)
 * @method static Expression raw($value)
 * @method static selectOne(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static array select(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static Generator cursor(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static bool insert(string $query, array $bindings = [])
 * @method static int update(string $query, array $bindings = [])
 * @method static int delete(string $query, array $bindings = [])
 * @method static bool statement(string $query, array $bindings = [])
 * @method static int affectingStatement(string $query, array $bindings = [])
 * @method static bool unprepared(string $query)
 * @method static array prepareBindings(array $bindings)
 * @method static transaction(\Closure $callback, int $attempts = 1)
 * @method static beginTransaction()
 * @method static rollBack()
 * @method static commit()
 * @method static int transactionLevel()
 * @method static array pretend(\Closure $callback)
 * @method static ConnectionInterface connection(string $pool)
 */
class DB
{
    /**
     * @var Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __call($name, $arguments)
    {
        if ($name === 'connection') {
            return $this->__connection(...$arguments);
        }
        return $this->__connection()->{$name}(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        $db = ContainerManager::getContainer()->make(Db::class,['container'=>ContainerManager::getContainer()]);
        /**
         * @var $db Db
         */
        if ($name === 'connection') {
            return $db->__connection(...$arguments);
        }
        return $db->__connection()->{$name}(...$arguments);
    }

    private function __connection($pool = 'default'): ConnectionInterface
    {
        $resolver = $this->container->make(ConnectionResolver::class,['container'=>$this -> container]);
        /**
         * @var $resolver ConnectionResolver
         */
        return $resolver->connection($pool);
    }
}
