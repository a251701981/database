<?php
namespace CloverSwoole\Database;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

/**
 * Class DbConnection
 * @package CloverSwoole\Database
 */
trait DbConnection
{
    public function table($table): Builder
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function raw($value): Expression
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }
    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function select($query, $bindings = [], $useReadPdo = true): array
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function cursor($query, $bindings = [], $useReadPdo = true): Generator
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function insert($query, $bindings = []): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function update($query, $bindings = []): int
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function delete($query, $bindings = []): int
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function statement($query, $bindings = []): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function affectingStatement($query, $bindings = []): int
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function unprepared($query): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function prepareBindings(array $bindings): array
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function transaction(Closure $callback, $attempts = 1)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function beginTransaction(): void
    {
        $this->setTransaction(true);
        $this->__call(__FUNCTION__, func_get_args());
    }

    public function commit(): void
    {
        $this->setTransaction(false);
        $this->__call(__FUNCTION__, func_get_args());
    }

    public function rollBack(): void
    {
        $this->setTransaction(false);
        $this->__call(__FUNCTION__, func_get_args());
    }

    public function transactionLevel(): int
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function pretend(Closure $callback): array
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }
}
