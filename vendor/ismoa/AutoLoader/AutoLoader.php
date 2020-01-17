<?php

class AutoLoader
{
    private static $instance;
    private $separator;
    private $root;
    private $alias;

    private function __construct()
    {
        $this->separator = DIRECTORY_SEPARATOR;
        $this->autoRegister();
        $this->setAlias();
    }

    private function setAlias()
    {
        $this->alias = array(
            'Artistic' => '.vendor.ismoa.Artistic.'
            ,'Route' => '.vendor.ismoa.Artistic.Router.'
            ,'Artistic/Router' => '.vendor.ismoa.Artistic.Router.'
            ,'Artistic/Routing' => '.vendor.ismoa.Artistic.Router.'
            ,'Request' => '.vendor.ismoa.Artistic.Http.'
            ,'Response' => '.vendor.ismoa.Artistic.Http.'
            ,'Helper' => '.vendor.ismoa.Artistic.Helper.'
            ,'View' => '.vendor.ismoa.Artistic.View.'
            ,'Storage' => '.vendor.ismoa.Artistic.Storage.'
            ,'Log' => '.vendor.ismoa.Artistic.Log.'
            ,'Env' => '.vendor.ismoa.Artistic.Support.'
            ,'Paginate' => '.vendor.ismoa.Artistic.Support.'
            ,'DB' => '.vendor.ismoa.Artistic.Database.'
            ,'Traits' => '.vendor.ismoa.Artistic.Traits.'
            ,'Translator' => '.vendor.ismoa.Artistic.Translate.'
            ,'Assist/Traits' => '.vendor.ismoa.Artistic.Traits.'
            ,'ArtisticException' => '.vendor.ismoa.Artistic.ArtisticException.'
            ,'Artistic/Http' => '.vendor.ismoa.Artistic.Http.'
            ,'App/Http/Controllers' => '.app.Http.Controllers.'
            ,'App/Http/Models' => '.app.Http.Models.'
            ,'App/Http/Views' => '.app.Http.Views.'
            );
    }

    private function setLoader($class)
    {
        $class = str_replace('\\', $this->separator, $class);
        $basename = basename($class);
        $namespace = str_replace('.', $basename, dirname($class));
        $root = realpath(__DIR__ . '/../../..');

        if (isset($this->alias[$namespace])) {
            $location = $root . str_replace('.', $this->separator, $this->alias[$namespace]) . $basename . '.php';
        } else {
            $toplevel = explode($this->separator, $namespace)[0];
            if (isset($this->alias[$toplevel])) {
                $fullname = $this->alias[$toplevel] . str_replace($toplevel . $this->separator, '', $namespace) . '.';
                $fullname = str_replace('.', $this->separator, $fullname) . $basename . '.php';
            } else {
                $fullname = $this->separator . $class . '.php';
            }
            $location = $root . $fullname;
        }
        return $location;
    }

    private function loadClass($class)
    {
        $location = $this->setLoader($class);
        if (file_exists($location)) require($location);
    }

    private function autoRegister()
    {
        spl_autoload_register(array($this, 'loadClass'), true, false);
    }

    public static function getInstance()
    {
        if(self::$instance == null) self::$instance = new AutoLoader;
        return self::$instance;
    }
}//end class