<?php
namespace CloverSwoole\Database;

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