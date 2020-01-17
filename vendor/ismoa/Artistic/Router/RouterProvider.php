<?php
namespace Artistic\Routing;

use Request;

class RouterProvider
{
    public static $route;
    public static $callback;
    public static $csrf;
    public static $argument;

    public static function request()
    {
        return Request::getInstance();
    }

    private static function getUri()
    {
        return self::request()->url;
    }

    private static function getMethod()
    {
        return self::request()->method;
    }

    private static function parseArguments(array $argument, array $data)
    {
        $args = array();
        foreach($argument as $key => $val) {
            $args[$val] = (isset($data[$val]) && $data[$val] != '') ? $data[$val] : '';
        }
        return $args;
    }

    private static function resolveParamter($refined = array(), $argument = array())
    {
        $resolve = array();
        foreach ($refined as $key => $val) {
            if(is_object($val)) {
                unset($argument[$key]);
                $resolve[$key] = $val;
            } else {
                if (isset($argument[$key])) {
                    $resolve[$key] = $argument[$key];
                    unset($argument[$key]);
                } else {
                    $resolve[$key] = $val;
                }
            }
        }

        if (count($argument) > 0) $resolve += $argument;

        return $resolve;
    }

    private static function refinedParameter($parameter)
    {
        $refined = array();
        foreach($parameter as $key => $reflect) {
            if(is_null($class = $reflect->getClass())) {
                if($reflect->isDefaultValueAvailable()) $refined[$reflect->getName()] = $reflect->getDefaultValue();
            } else {
                  $refined[$class->name] = ($class->name == 'Request') ? self::request() : new $class->name;
            }
        }
        return $refined;
    }

    private static function reflectConstruct(\ReflectionClass $Class)
    {
        $construct = $Class->getConstructor();
        $parameter = $construct->getParameters();
        $refined = self::refinedParameter($parameter);
        $resolve = self::resolveParamter($refined);

        return $Class->newInstanceArgs($resolve);
    }

    private static function reflectMethod(\ReflectionClass $Class, $method, $argument, $instance)
    {
        $method = $Class->getMethod($method);
        $parameter = $method->getParameters();
        $refined = self::refinedParameter($parameter);
        $resolve = self::resolveParamter($refined, $argument);

        return $method->invokeArgs($instance, $resolve);
    }

    private static function reflectClass($class, $method, $argument)
    {
        $Class = new \ReflectionClass($class);
        $instance = self::reflectConstruct($Class);

        return self::reflectMethod($Class, $method, $argument, $instance);
    }

    private static function reflectClosure(\Closure $closure, $argument)
    {
        $Closure = new \ReflectionFunction($closure);
        $parameter = $Closure->getParameters();
        $refined = (count($parameter) > 0) ? self::refinedParameter($parameter): array();
        $resolve = self::resolveParamter($refined, $argument);

        return $Closure->invokeArgs($resolve);
    }

    private static function callback($callback, $csrf, $argument = array())
    {
        if (true === $csrf) self::request()->csrfSecurity();
        if (is_callable($callback) && $callback instanceof \Closure) {
            return self::reflectClosure($callback, $argument);
        } else {
            try {
                if (false === strpos($callback, '@')) throw new \ArtisticException('callback error', 404);
                list($class, $method) = explode('@', $callback);

                $Controller = '\\App\\Http\\Controllers\\' . $class;

                if (false === class_exists($Controller, true))
                    throw new \ArtisticException('class not found ' . $Controller, 500);

                if (false === method_exists($Controller, $method)) 
                    throw new \ArtisticException('method not found ' . $Controller, 500);

                return self::reflectClass($Controller, $method, $argument);
            } catch (\ArtisticException $E) {
                $E->getException();
            } 
        }
    }

    public static function match()
    {
        $method = self::getMethod();
        $routes = isset(self::$route[$method]) ? self::$route[$method] : array();
        $callback = isset(self::$callback[$method]) ? self::$callback[$method] : array();
        $csrf = isset(self::$csrf[$method]) ? self::$csrf[$method] : array();

        $url = trim(self::getUri(), '/');

        foreach ($routes as $key => $route) {

            $route = trim($route , '/');

            if (!isset($callback[$key]) || (is_string($callback[$key]) && strlen($callback[$key]) < 1)) 
                throw new \ArtisticException('The callback is not defined. route url : '. $route, 404);

            $is_conv = (false === strpos($route, '{')) ? false : true;

            if ((!$is_conv && $route != $url) || ($is_conv && !(bool)
                preg_match_all('/{([\w]+)(\??)}/u', $route, $args))) continue;

            if ($url == $route) 
                if(isset($callback[$key])) return self::callback($callback[$key], $csrf[$key]);
             if (count($args) > 0) {
                $split = preg_split('/((\-?\/?)\{[^}]+\})/', $route);
                $count = count($args[1]);
                $union = '';
                $separator = '(\/|\-)';
                foreach ($split as $skey => $item) {
                    $conv = '';
                    if ($skey < $count) {
                        $name = $args[1][$skey];
                        $regex = ($skey == 0 && strlen($item) < 1) ? '' : $separator;
                        $format = '(%1$s(?P<%2$s>[\w|\.?|\s]+))' . $args[2][$skey];
                        $conv = sprintf($format, $regex, $name);
                    }
                    $union .= preg_quote($item, '/') . $conv;
                }
            }
            $regex = '/^' . $union . '\/?$/u';
            if (true === (bool)preg_match($regex, rawurldecode($url), $data)) {
                $parameter = (isset($args[1])) ? self::parseArguments($args[1], $csrf[$key], $data) : array();
                return self::callback($callback[$key], $parameter);
            }
        }

        $msg =  'REQUEST URL : '. $url . PHP_EOL . 
                'REQUEST IP : ' . self::request()->header['remote_addr'] . PHP_EOL .
                'REQUEST AGENT : ' .self::request()->header['http_user_agent'] . PHP_EOL; 
        throw new \ArtisticException('Unregistered URL' . PHP_EOL . $msg, 404);
    }

    public static function add($method, $route, $callback, $csrf)
    {
        self::$route[$method][] = $route;
        self::$callback[$method][] = $callback;
        self::$csrf[$method][] = $csrf;
    }
}//end class