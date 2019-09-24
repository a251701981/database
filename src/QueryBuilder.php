<?php

namespace CloverSwoole\Database;

use Illuminate\Database\Query\Builder;

/**
 * Class QueryBuilder
 * @package CloverSwoole\Database
 */
class QueryBuilder extends Builder
{
    /**
     * 缓存时长
     * @var int
     */
    protected $rememberTime = 0;
    /**
     * The database connection instance.
     *
     * @var MySqlConnection
     */
    public $connection;

    /**
     * Run the query as a "select" statement against the connection.
     *
     * @return array
     */
    protected function runSelect()
    {
        if ($this->rememberTime != 0) {
            return $this->connection->selectRemember(
                $this->toSql(), $this->getBindings(), !$this->useWritePdo, $this->rememberTime
            );
        } else {
            return $this->connection->select(
                $this->toSql(), $this->getBindings(), !$this->useWritePdo
            );
        }
    }

    /**
     * 设置缓存时间
     * @param int $time
     * @return $this
     */
    public function remember($time = 0)
    {
        $this->rememberTime = $time;
        return $this;
    }
}