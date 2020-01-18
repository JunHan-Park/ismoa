<?php

use Artistic\Http\RequestProvider;

class Request extends RequestProvider
{
    use \Artistic\Traits\Singleton;

    public function __construct()
    {
        parent::__construct();
    }

    public function input()
    {
        return isset($this->variable[$this->method]) ? $this->variable[$this->method] : array();
    }

    public function __get($name){
        if (isset($this->{$name})) return $this->{$name};
    }

}//end class