<?php

namespace CloverSwoole\Database;
/**
 * Interface ConnectorInterface
 * @package CloverSwoole\Database
 */
interface ConnectorInterface
{
    /**
     * Establish a database connection.
     *
     * @param  array  $config
     * @return \PDO
     */
    public function connect(array $config);
}
