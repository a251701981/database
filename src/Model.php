<?php
namespace CloverSwoole\Database;
use Illuminate\Database\Query\Builder;

/**
 * Class Model
 * @package CloverSwoole\Database
 * @mixin Builder
 */
class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * 获取容器
     * @return \Illuminate\Container\Container|null
     */
    protected function getContainer()
    {
        return ContainerManager::getContainer();
    }

    /**
     * 获取一个这个模型的连接
     * @return ConnectionInterface
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
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
        $resolver = $this->getContainer()->make(ConnectionResolver::class,['container'=>$this -> getContainer()]);
        /**
         * 获取连接
         * @var $resolver \CloverSwoole\Database\ConnectionResolver
         */
        return $resolver->connection($connectionName);
    }
}