<?php

use Artistic\Routing\RouterProvider;

class Route extends RouterProvider
{
    public static function put($route, $callback, $csrf = false)
    {
        self::add('put' , $route, $callback, $csrf);
    }

    public static function patch($route, $callback, $csrf = false)
    {
        self::add('patch' , $route, $callback, $csrf);
    }

    public static function delete($route, $callback, $csrf = false)
    {
        self::add('delete' , $route, $callback, $csrf);
    }

    public static function get($route, $callback, $csrf = false)
    {
        self::add('get' , $route, $callback, $csrf);
    }

    public static function post($route, $callback, $csrf = false)
    {
        self::add('post', $route, $callback, $csrf);
    }

    public static function run()
    {
        if (is_object($route =  self::match())) return $route;
        else echo $route;
    }
}
