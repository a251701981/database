<?php

namespace CloverSwoole\Database;

use CloverSwoole\Database\Utils\Arr;
use Doctrine\DBAL\Driver\PDOMySql\Driver as DoctrineDriver;
use Illuminate\Database\Query\Grammars\MySqlGrammar as QueryGrammar;
use Illuminate\Database\Query\Processors\MySqlProcessor;
use Illuminate\Database\Schema\Grammars\MySqlGrammar as SchemaGrammar;
use Illuminate\Database\Schema\MySqlBuilder;
use PDO;
use InvalidArgumentException;

/**
 * Class MySqlConnection
 * @package CloverSwoole\Database
 */
class MySqlConnection extends \Illuminate\Database\Connection
{
    /**
     * Get the default query grammar instance.
     *
     * @return \CloverSwoole\Database\Query\Grammars\MySqlGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar);
    }

    function query()
    {
        return new QueryBuilder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \CloverSwoole\Database\Schema\MySqlBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new MySqlBuilder($this);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \CloverSwoole\Database\Schema\Grammars\MySqlGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar);
    }

    /**
     * Get the default post processor instance.
     *
     * @return \CloverSwoole\Database\Query\Processors\MySqlProcessor
     */
    protected function getDefaultPostProcessor()
    {
        return new MySqlProcessor;
    }

    /**
     * Get the Doctrine DBAL driver.
     *
     * @return \Doctrine\DBAL\Driver\PDOMySql\Driver
     */
    protected function getDoctrineDriver()
    {
        return new DoctrineDriver;
    }

    /**
     * @param $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @param int $rememberTime
     * @return mixed
     */
    public function selectRemember($query, $bindings = [], $useReadPdo = true, int $rememberTime)
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo,$rememberTime) {
            if ($this->pretending()) {
                return [];
            }
            $config = ContainerManager::getContainer() -> make(ConfigInterface::class);
            /**
             * 拼接出连接的key
             */
            $key = sprintf('databases.%s', $this -> getName());
            /**
             * 判断连接的配置是否存在
             */
            if (!$config->has($key)) {
                throw new InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
            }
            /**
             * 获取连接池选项
             */
            $class = Arr::get($this->config, 'cache','');
            if(class_exists($class)){
                $cache = new $class();
                if($cache instanceof CacheInterface){
                    if($cache -> has($query,$bindings)){
                        return $cache -> get($query,$bindings);
                    }else{
                        // For select statements, we'll simply execute the query and return an array
                        // of the database result set. Each element in the array will be a single
                        // row from the database table, and will either be an array or objects.
                        $statement = $this->prepared($this->getPdoForSelect($useReadPdo)
                            ->prepare($query));

                        $this->bindValues($statement, $this->prepareBindings($bindings));

                        $statement->execute();
                        /**
                         * 解析结果
                         */
                        $result = $statement->fetchAll();
                        /**
                         * 设置缓存
                         */
                        $cache -> set($query,$bindings,$result,$rememberTime);
                        /**
                         * 返回结果
                         */
                        return $result;
                    }
                }
            }
        });
    }

    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }
            // For select statements, we'll simply execute the query and return an array
            // of the database result set. Each element in the array will be a single
            // row from the database table, and will either be an array or objects.
            $statement = $this->prepared($this->getPdoForSelect($useReadPdo)
                ->prepare($query));

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $statement->execute();

            return $statement->fetchAll();
        });
    }

    /**
     * Bind values to their parameters in the given statement.
     *
     * @param \PDOStatement $statement
     * @param array $bindings
     * @return void
     */
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1, $value,
                is_int($value) || is_float($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }
}
