<?php

class Env
{
    private static $config;

    public static function setLocale($locale)
    {
        setcookie('locale', $locale, time() + 315360000, '/');
    }

    public static function getLocale()
    {
        if (isset($_COOKIE['locale'])) return $_COOKIE['locale'];
        else {
            self::getConfig();
            $lang = isset(self::$config['locale']) ? self::$config['locale'] : 'kr';
            return $lang;
        }
    }

    public static function isLocale($locale)
    {
        return (self::getLocale() == $locale) ? true : false;
    }

    public static function getConfig()
    {
        if(!file_exists(($config = realpath(__DIR__.'/../../../../config/config.php'))))
            throw new \ArtisticException('Config Not Found', 500);

        self::$config = require($config);

        return self::$config;
    }
}
