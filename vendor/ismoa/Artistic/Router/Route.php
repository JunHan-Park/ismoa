<?php

use Artistic\Routing\RouterProvider;

class Route extends RouterProvider
{
    public static function put($route, $callback, $csrf = false, $sess = true)
    {
        self::add('put' , $route, $callback, $csrf, $sess);
    }

    public static function patch($route, $callback, $csrf = false, $sess = true)
    {
        self::add('patch' , $route, $callback, $csrf, $sess);
    }

    public static function delete($route, $callback, $csrf = false, $sess = true)
    {
        self::add('delete' , $route, $callback, $csrf, $sess);
    }

    public static function get($route, $callback, $csrf = false, $sess = true)
    {
        self::add('get' , $route, $callback, $csrf, $sess);
    }

    public static function post($route, $callback, $csrf = false, $sess = true)
    {
        self::add('post', $route, $callback, $csrf, $sess);
    }

    public static function run()
    {
        if (is_object($route =  self::match())) return $route;
        else echo $route;
    }
}