<?php

class ArtisticException Extends \Exception
{
    private $develop;
    private $error;
    private $directory;

    public function __construct($message, $code = 0, $previous = null) {

        $config = Env::getConfig();

        $this->develop = isset($config['develop']) ? $config['develop'] : true;

        $this->directory = realpath(__DIR__ . '/../../Assist/error');

        parent::__construct($message, $code, $previous);
        $this->getException();
    }

    private function parseException()
    {
        $this->error = array();
        $this->error['develop'] = $this->develop;
        $this->error['msg'] = $this->getMessage();
        $this->error['code'] = $this->getCode();
        $this->error['file'] = $this->getFile();
        $this->error['line'] = $this->getLine();
        $this->error['string'] = $this->getTraceAsString();
    }

    public function getException()
    {
        $this->parseException();

        $context = ' Uncaught Exception: ' 
            . $this->error['msg'] 
            . ' in ' 
            . $this->error['file']
            . ':'
            . $this->error['line'] 
            . PHP_EOL
            . $this->error['string'];

        Log::error($context);
        $view = $this->directory . '/exception.html';
        http_response_code($this->error['code']);

        if (file_exists($view)) {
            extract($this->error);
            die(require_once($view));
        }
    }

}//end class