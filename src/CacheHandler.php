<?php
namespace CloverSwoole\Database;
use Redis;

/**
 * 缓存处理器
 * Class CacheHandler
 */
class CacheHandler implements CacheInterface
{
    /**
     * @var Redis
     */
    protected $redis = null;
    public function __construct()
    {
        $this -> redis = new Redis();
        $this -> redis -> connect('127.0.0.1',6379);
    }
    protected function getKey($query,$bindings)
    {
        return md5($query.serialize($bindings));
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