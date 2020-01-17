<?php

use Artistic\Security\CsrfSecurity;

class Helper
{
    private $allow_method = array();
    private $config;
    private $separator = DIRECTORY_SEPARATOR;
    private $default = '';

    use \Artistic\Traits\Singleton;

    public function __construct()
    {
        $this->setMethod();
    }

    private function setMethod()
    {
        $default = $this->getConfig('config.method');
        $method = (is_array($default) && count($default) > 0) ? $default : array();

        $this->allow_method = array();
        foreach ($method as $key => $val) {
            array_push($this->allow_method, $val);
        }
    }

    private function onLocation($uri)
    {
        header('Location: ' . $uri);
    }

    private function buildDomain()
    {
        return $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_X_HOST'];
    }

    private function buildQuery(array $qrst)
    {
        return (count($qrst) > 0) ? '?' . http_build_query($qrst) : '';
    }

    private function solvArray($array, $key)
    {
        foreach ($key as $segment) {
            if (isset($array[$segment])) $array = $array[$segment];
            else return '';
        }

        return $array;
    }

    public function redirect($uri = '/', $qrst = array())
    {
        if (strlen($uri) > 0) { 
            $uri = $this->buildDomain() 
                . $this->separator
                . str_replace('.', $this->separator, trim($uri, '.')) 
                . $this->buildQuery($qrst);
            $this->onLocation(rtrim($uri,'/'));
        }
        return $this;
    }

    public function back($uri, $qrst = array())
    {
        $referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : false;
        $uri = (strlen($uri) > 0) ? str_replace('.', $this->separator, trim($uri, '.'))  : false;

        if (isset($_SESSION['artistic']['input'])) unset($_SESSION['artistic']['input']);
        $_SESSION['artistic']['input'] = $_POST;

        if ($uri) { 
            $uri = $this->buildDomain() . $this->separator . $uri . $this->buildQuery($qrst);
        }else{
            if ($referer) $uri = trim($referer, $this->separator) . $this->buildQuery($qrst);
            else $uri = $this->buildDomain();
        }

        $this->onLocation($uri);
        return $this;
    }

    public function withMsg($msg)
    {
        if (strlen($msg) > 0) $_SESSION['msg'] = $msg;

        return $this;
    }

    public static function getCsrfToken()
    {
        return CsrfSecurity::getInstance()->getToken(Request::getInstance());
    }

    public function getConfig($key)
    {
        $dir =  str_replace('/', $this->separator, '/../../../../config');

        if (!is_dir(($dir = realpath(__DIR__ . $dir)))) return $this->default;
 
        $key = explode('.', $key);
        $file = $key[0];

        if (!file_exists($include = $dir . $this->separator . $file . '.php')) return 'file not found ' . $file;

        $array = include($include);
        array_shift($key);

        if (count($key) > 0) return $this->solvArray($array, $key);
        else return $array;
    }

    public function pastInput($key)
    {
        $key = explode('.', $key);
        $array = isset($_SESSION['artistic']['input']) ? $_SESSION['artistic']['input'] : array();
        return $this->solvArray($array, $key);
    }

}//end class
