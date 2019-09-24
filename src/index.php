<?php

use CloverSwoole\Database\CacheInterface;
use Illuminate\Container\Container;
use CloverSwoole\Database\ConfigInterface;
use CloverSwoole\Database\ContainerManager;
use Redis;

include_once __DIR__."/../vendor/autoload.php";
/**
 * 数据库配置
 * Class DbConfig
 */
class DbConfig implements ConfigInterface
{
    private $config = [
        'databases'=>[
            'default' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'database' => 'chat',
                'username' => 'root',
                'password' => '123456',
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'pool' => [
                    'min_connections' => 1,
                    'max_connections' => 10,
                    'connect_timeout' => 10.0,
                    'wait_timeout' => 3.0,
                    'heartbeat' => -1,
                    'max_idle_time' => 60.00,
                ],
                'cache' => CacheHandler::class,
            ],
        ]
    ];

    public function get(string $key, $default = null)
    {
        return self::findVarByExpression($key,$this -> config);
    }
    public function set(string $key, $value)
    {
        $this -> config[$key] = $value;
    }
    public function has(string $keys)
    {
        return boolval(self::findVarByExpression($keys,$this ->config));
    }
    /**
     * 根据表达式查找变量
     * @param null|string $expression
     * @param array $var
     * @return array|mixed
     */
    protected static function findVarByExpression($expression = null, $var = [])
    {
        if ($expression === null || strlen($expression) < 1) {
            return $var;
        }
        $array = self::query_expression(strval($expression));
        if (count(is_array($array) ? $array : []) >= 1) {
            foreach ($array as $key => $item) {
                if ($item == null || $item == '' || strlen($item) < 1) {
                    return $var;
                }
                $var = isset($var[$item]) ? $var[$item] : null;
                if ($var === null) {
                    return null;
                }
            }
        }
        return $var;
    }

    /**
     * 查询处理字符串
     * @param string $string
     * @return array
     */
    protected static function query_expression(string $string = '')
    {
        return explode('.', $string);
    }
}

/**
 * 缓存处理器
 * Class CacheHandler
 */
class CacheHandler implements CacheInterface
{
    protected $config = [];
    /**
     * @var Redis
     */
    protected $redis = null;
    public function __construct()
    {
        $this -> redis = new Redis();
        $this -> redis -> connect('127.0.0.1',6379);
        $config = [
            'cache_key' => '{$sql}-{$bindings}', // querySql,$bindings
            'prefix' => 'default',
        ];
    }
    protected function getKey($query,$bindings)
    {
        return $query.serialize($bindings);
    }
    public function get($query,$bindings)
    {
        return unserialize($this -> redis -> get($this -> getKey($query,$bindings)));
    }
    public function set($query,$bindings,$data,int $rememberTime = -1)
    {
        return $this -> redis -> setex($this -> getKey($query,$bindings),$rememberTime,serialize($data));
    }
    public function has($query,$bindings):bool
    {
        return $this -> redis -> exists($this -> getKey($query,$bindings));
    }
    public function del($query,$bindings)
    {
        return $this -> redis -> del($this -> getKey($query,$bindings));
    }
}

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
    $res = Users::remember(60) -> where('id','>',0) -> get() -> toArray();
//    $res = \CloverSwoole\Database\DB::table('users') -> where('id','>',0) -> get();
    var_dump($res);
}catch (\Throwable $throwable){
    var_dump('异常:'.$throwable);
}
