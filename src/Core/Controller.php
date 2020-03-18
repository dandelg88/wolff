<?php

namespace Wolff\Core;

use Wolff\Utils\Str;

class Controller
{

    const NAMESPACE = 'Controller\\';
    const EXISTS_ERROR = 'The controller class \'%s\' doesn\'t exists';
    const METHOD_EXISTS_ERROR = 'The controller class \'%s\' doesn\'t have a \'%s\' method';
    const PATH_FORMAT = '%s/' . CORE_CONFIG['controllers_dir'] . '/%s.php';


    /**
     * Returns the controller with the giving name
     *
     * @param  string  $path  the controller path
     *
     * @return \Wolff\Core\Controller the controller
     */
    public static function get(string $path)
    {
        $path = Str::sanitizePath($path);

        //load controller default function and return it
        if (($controller = Factory::controller($path)) === false) {
            throw new \Error(sprintf(self::EXISTS_ERROR, $path, $method));
        }

        return $controller;
    }


    /**
     * Returns the return value of the controller's method
     * or null in case of errors
     *
     * @param  string  $path  the controller path
     * @param  string  $method  the controller method
     * @param  array  $args  the method arguments
     *
     * @return mixed the return value of the controller's method
     * or null in case of errors
     */
    public static function method(string $path, string $method = 'index', array $args = [])
    {
        $controller = Factory::controller($path);

        if (!method_exists($controller, $method)) {
            throw new \Error(sprintf(self::METHOD_EXISTS_ERROR, $path, $method));
        }

        return call_user_func_array([$controller, $method], $args);
    }


    /**
     * Returns the complete path of the controller
     *
     * @param  string  $path  the path of the controller
     *
     * @return string the complete path of the controller
     */
    public static function getPath(string $path)
    {
        return sprintf(self::PATH_FORMAT, CONFIG['app_dir'], $path);
    }


    /**
     * Returns true if the controller exists,
     * false otherwise
     *
     * @param  string  $path  the path of the controller
     *
     * @return boolean true if the controller exists, false otherwise
     */
    public static function exists(string $path)
    {
        return file_exists(self::getPath($path));
    }


    /**
     * Returns true if the controller's method exists, false otherwise
     *
     * @param  string  $path  the controller path
     * @param  string  $method  the controller method name
     *
     * @return boolean true if the controller's method exists, false otherwise
     */
    public static function hasMethod(string $path, string $method)
    {
        $class = self::NAMESPACE . str_replace('/', '\\', $path);

        if (!class_exists($class)) {
            return false;
        }

        $class = new \ReflectionClass($class);

        if (!$class->hasMethod($method)) {
            return false;
        }

        return true;
    }

}