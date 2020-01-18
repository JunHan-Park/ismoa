<?php

use Artistic\Http\RequestProvider;
use Artistic\Security\CsrfSecurity;

class Request extends RequestProvider
{
    use \Artistic\Traits\Singleton;

    public function __construct()
    {
        parent::__construct();
    }

    public function csrfSecurity()
    {
        try{
            CsrfSecurity::getInstance()->verifyCsrf($this);
        }catch(\ArtisticException $E) {
            $E->getException();
        }
    }

    public function input()
    {
        return isset($this->variable[$this->method]) ? $this->variable[$this->method] : array();
    }

    public function __get($name){
        if (isset($this->{$name})) return $this->{$name};
    }

}//end class