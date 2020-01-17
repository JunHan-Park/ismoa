<?php
namespace Artistic\Traits;

trait Singleton
{
   public static $instance = null;

   public static function getInstance()
    {
        if (is_null(self::$instance)) self::$instance = new Self;
        return self::$instance;
    }
   
}