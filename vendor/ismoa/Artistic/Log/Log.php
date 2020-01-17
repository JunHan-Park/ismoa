<?php
use Artistic\Log\Logger;

class Log extends Logger
{
    use \Artistic\Traits\Singleton;
    
    public function __construct()
    {
        parent::__construct();
    }

    public static function debug($msg)
    {
        $message = array(date('Y-m-d H:i:s'), 'DEBUG', $msg);
        return self::getInstance()->writeLog($message);
    }

    public static function error($msg)
    {
        if (is_array($msg)) {
            $message = array(date('Y-m-d H:i:s'));
            $message = array_merge($message, $msg);
        } else {
            $message = array(date('Y-m-d H:i:s'), 'ERROR', $msg);
        }

        return self::getInstance()->writeLog($message);
    }
}//end class