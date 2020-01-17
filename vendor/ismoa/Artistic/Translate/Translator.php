<?php

use Artistic\Support\App;

class Translator
{
    private $root;
    private $index;
    private $locale;
    private $table; 
    private $package;
    private $parameter;
    private $message;

    use \Artistic\Traits\Singleton;

    public function __construct()
    {
        $this->setRoot();
        $this->getLocale();
    }

    private function setRoot()
    {
        $this->root = realpath(__DIR__ . '/../../../../app/Lang');
    }

    private function getLocale()
    {
        $this->locale = Env::getLocale();
    }

    private function splitTranslate()
    {
        list($translations , $this->index) = explode('.', $this->package);
        $this->loadTable($translations);
    }

    private function loadTable($translations)
    {
        if (!file_exists($path = $this->root . '/' . $this->locale . '/' . $translations .'.php'))
            throw new \ArtisticException('Translate file not found.', 500);
        $this->table = require($path);
    }

    private function matchPalceHolder()
    {
        preg_match_all('/=?:(\w+)/u', $this->message, $matches);
        if (count($matches[1]) > 0) {
            $args = array();
            foreach ($matches[1] as $key => $text) {
                if (isset($this->parameter[$text])) {
                    $this->message = str_replace($matches[0][$key], $this->parameter[$text], $this->message);
                } else {
                    throw new \ArtisticException('There are no matching values.', 500);
                    break;
                }
            }
        }
    }

    private function translateMessage()
    {
        if (preg_match('/=?:(\w+)/u', $this->message)) $this->matchPalceHolder();
    }

    public function activeTranslate(string $package , $parameter = array())
    {
        if (false === strpos($this->package = $package, '.')) return $this->package;
        $this->parameter = $parameter;
        $this->splitTranslate();

        if (!isset($this->table[$this->index])) return $this->package;
        $this->message = $this->table[$this->index];
        $this->translateMessage();

        return $this->message;
    }
}//end class