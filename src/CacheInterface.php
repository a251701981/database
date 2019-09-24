<?php
namespace CloverSwoole\Database;

/**
 * Interface CacheInterface
 * @package CloverSwoole\Database
 */
interface CacheInterface
{
    public function set($query,$bindings,$data,int $rememberTime = -1);
    public function get($query,$bindings);
    public function has($query,$bindings):bool;
    public function del($query,$bindings);
}