<?php

namespace FluentGemini\Framework\Database;

use FluentGemini\Framework\Foundation\App;
use FluentGemini\Framework\Database\ConnectionResolverInterface;

class ConnectionResolver implements ConnectionResolverInterface
{
    /**
     * Get a database connection instance.
     *
     * @param  string  $name
     * @return \FluentGemini\Framework\Database\ConnectionInterface
     */
    public function connection($name = null)
    {
        return App::getInstance('db');
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        // Pass
    }

    /**
     * Set the default connection name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultConnection($name)
    {
        // Pass
    }
}
