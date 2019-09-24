<?php

namespace CloverSwoole\Database;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use CloverSwoole\Database\QueryBuilder;
use Throwable;

/**
 * Class Model
 * @package CloverSwoole\Database
 * @mixin QueryBuilder
 */
class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * 获取容器
     * @return Container|null
     */
    protected function getContainer()
    {
        return ContainerManager::getContainer();
    }

    /**
     * 获取一个这个模型的连接
     * @return ConnectionInterface
     * @throws BindingResolutionException
     * @throws Throwable
     */
    public function getConnection(): ConnectionInterface
    {
        /**
         * 获取本模型的连接名称
         */
        $connectionName = $this->getConnectionName();
        /**
         * 创建连接解析器
         */
        $resolver = $this->getContainer()->make(ConnectionResolver::class, ['container' => $this->getContainer()]);
        /**
         * 获取连接
         * @var $resolver ConnectionResolver
         */
        return $resolver->connection($connectionName);
    }
}