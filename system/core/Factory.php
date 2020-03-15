<?php

namespace Core;

use Utilities\Str;
use PDO, PDOException;

class Factory
{

    const NAMESPACE_CONTROLLER = 'Controller\\';
    const NAMESPACE_MIDDLEWARE = 'Middleware\\';
    const DSN = '{dbms}:host={server}; dbname={db}';
    const DEFAULT_ENCODING = 'set names utf8mb4 collate utf8mb4_unicode_ci';


    /**
     * Returns a PDO connection or false in case of errors
     *
     * @param  array  $options  the connection options
     *
     * @return PDO|bool a PDO connection or false in case of errors
     */
    public static function connection(array $options)
    {
        if (empty($options) || empty(CONFIG['db'])) {
            return false;
        }

        $dsn = Str::interpolate(self::DSN, [
            'dbms'   => CONFIG['dbms'] ?? '',
            'server' => CONFIG['server'] ?? '',
            'db'     => CONFIG['db'] ?? '',
        ]);

        $username = CONFIG['db_username'] ?? '';
        $password = CONFIG['db_password'] ?? '';

        try {
            $connection = new PDO($dsn, $username, $password, $options);
            self::setEncoding($connection);
        } catch (PDOException $e) {
            Log::critical($e->getMessage());

            return false;
        }

        return $connection;
    }


    /**
     * Returns a controller initialized or false if it doesn't exists
     *
     * @param  string  $dir  the controller directory
     *
     * @return object|bool a controller initialized or false if it doesn't exists
     */
    public static function controller(string $dir = null)
    {
        //Load default Controller
        if (!isset($dir)) {
            return new Controller;
        }

        $class = self::NAMESPACE_CONTROLLER . str_replace('/', '\\', $dir);

        if (!class_exists($class)) {
            Log::error("The controller class '$dir' doesn't exists");

            return false;
        }

        return new $class;
    }


    /**
     * Returns a middleware initialized or false if it doesn't exists
     *
     * @param  string  $name  the middleware name
     *
     * @return object|bool a middleware initialized or false if it doesn't exists
     */
    public static function middleware(string $name)
    {
        $class = self::NAMESPACE_MIDDLEWARE . $name;

        if (!class_exists($class)) {
            Log::error("The middleware class '$name' doesn't exists");

            return false;
        }

        return new $class;
    }


    /**
     * Returns a query result as an object
     *
     * @param \PDOStatement $results
     * @return Query a query result as an object
     */
    public static function query($results)
    {
        return new Query($results);
    }


    /**
     * Set the default encoding for the connection
     *
     * @param  \PDO  $connection  the connection
     */
    private static function setEncoding($connection)
    {
        $connection->prepare(self::DEFAULT_ENCODING)->execute();
    }

}
