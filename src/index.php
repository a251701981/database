<?php
use CloverSwoole\Database\DbConfig;
use Illuminate\Container\Container;
use CloverSwoole\Database\ConfigInterface;
use CloverSwoole\Database\ContainerManager;

include_once __DIR__."/../vendor/autoload.php";


/**
 * 测试模型
 * Class Users
 */
class Users extends \CloverSwoole\Database\Model
{
    protected $table = 'users';
}

try{
    /**
     * 容器
     */
    $container = new Container();
    /**
     * 注入配置
     */
    $container -> bind(ConfigInterface::class,DbConfig::class);
    /**
     * 设置容器
     */
    ContainerManager::setContainer($container);
    /**
     * 查询数据
        */
//    $res = Users::remember(60) -> where('id','>',0) -> get() -> toArray();
    $res = \CloverSwoole\Database\DB::table('users') -> remember(60) -> where('id','>',0) -> get();
    var_dump($res);
}catch (\Throwable $throwable){
    var_dump('异常:'.$throwable);
}
