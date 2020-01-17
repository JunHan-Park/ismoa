<?php
namespace Artistic\Log;

class Logger
{
    private $root;
    private $log;

    public function __construct()
    {
       $this->root = realpath(__DIR__ .  '/../../../../storage/log');
       $this->directory = date('Y');

       if (false === $this->root) $this->makeRoot();
    }

    private function makeDirectory($dir, $permission)
    {
        umask(0);
        return mkdir($dir, $permission, true);
    }

    private function makeRoot()
    {
        $root = realpath(__DIR__ .'/../../../../') . '/storage/log';

        if (!is_dir($root)) {
            $this->makeDirectory($root, 0755);
            $this->root = $root;
        }
    }

    public function writeLog($message)
    {
        $text = vsprintf('[%s] %s. %s', $message);
        $log = sprintf('log_%s.log',date('Ymd'));
        error_log($text . PHP_EOL, 3 , $this->root . '/' . $log);
    }
}//end class