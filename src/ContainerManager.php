<?php
namespace CloverSwoole\Database;

use Illuminate\Container\Container;

/**
 * 容器管理
 * Class ContainerManager
 * @package CloverSwoole\Database
 */
class ContainerManager
{
    /**
     * @var null | Container
     */
    private static $container = null;

    /**
     * 获取容器
     * @return Container|null
     */
    public static function getContainer()
    {
        if(!(self::$container instanceof Container)){
            self::$container = new Container();
        }
        return self::$container;
    }

    /**
     * 是否初始化了容器
     * @return bool
     */
    public static function hasContainer()
    {
        return self::$container != null && self::$container instanceof Container;
    }

    /**
     * 设置容器
     * @param Container $container
     */
    public static function setContainer(Container $container)
    {
        self::$container = $container;
    }
}