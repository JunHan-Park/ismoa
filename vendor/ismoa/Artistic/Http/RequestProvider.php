<?php
namespace Artistic\Http;

use Artistic\Http\Input;

class RequestProvider
{
    private $allow_method;

    private $override_method;
    protected $origin_method;
    protected $method;
    protected $url;
    protected $variable;
    protected $header;

    public function __construct()
    {
        $this->allow_method = array('get', 'post'); 
        $this->header = array();
        $this->parseHeader();
        $this->setMethod();
        $this->parseMethod();
        $this->parseUri();
    }

    private function setMethod()
    {
        $config = \Env::getConfig();
        $method = isset($config['method']) ? $config['method'] : array();
        foreach ($method as $key => $val) {
            array_push($this->allow_method, $val);
        }
    }

    private function parseHeader()
    {
        $this->header = array();
        foreach ($_SERVER as $key => $val) {
            $this->header[strtolower($key)] = $val;
        }

        $apache_request_headers = apache_request_headers();
        if (count($apache_request_headers) > 0) {
            foreach ($apache_request_headers as $key => $val) {
                $key = strtolower($key);
                if (isset($this->header[$key])) continue;
                $this->header[$key] = $val;
            }
        }
    }

    private function verifyMethod()
    {
        try {
            if ($this->override_method == true && $this->origin_method != 'post')
                throw new \ArtisticException('Method overriding is only POST support.', 500);

            if (true !== in_array($this->method, $this->allow_method))
                throw new \ArtisticException('It is not a usable http method.', 500);
        } catch(ArtisticException $E) {
            $E->getException();
        }
    }

    public function parseMethod()
    {
        $this->method = $this->origin_method = 
            isset($this->header['request_method']) ? strtolower($this->header['request_method']) : 'get';

        if ($this->method == 'post') $this->overrideMethod();
        $this->verifyMethod();
        $this->resolveVariable();
    }

    public function overrideMethod()
    {
        if (isset($_POST['_method']) && $_POST['_method'] != 'post') {
            $this->method = isset($_POST['_method']) ? strtolower($_POST['_method']) : $this->method;
            $this->override_method = true;
        }
    }

    public function parseUri()
    {
        $uri = (isset($this->header['request_uri'])) ? $this->header['request_uri'] : '';
        $parse = parse_url($uri);

        if ($parse['path'] != '/') {
            $uri = trim($parse['path'], '/');
            $uri = (false !== strpos($uri, 'public/')) ? str_replace('public/', '', $uri) : $uri;
        } else {
            $uri = $parse['path'];
        }

        $this->url = $uri;
    }

    public function resolveVariable()
    {
        $this->{$this->method . 'Variable'}();
    }

    private function initVariable()
    {
        $this->variable[$this->method] = array();
    }

    private function matchGlobal($input)
    {
        $GLOBALS['_' . strtoupper($this->origin_method)] = $input;
    }

    private function setVariable($input)
    {
        $this->parseHeader();
        $this->matchGlobal($input);
        if (is_array($input) && count($input) > 0) {
            array_walk_recursive($input, 'trim');
        }
        $this->variable[$this->method] = $input;
    }

    private function getVariable()
    {
        if (count($_GET) > 0) $this->setVariable($_GET);
    }

    private function postVariable()
    {
        if (count($_POST) > 0) $this->setVariable($_POST);
    }

    private function deleteVariable()
    {
        if ($this->origin_method == 'delete') $this->setVariable($this->getRestInput(new Input));
        else $this->setVariable($_POST);
    }

    private function putVariable()
    {
        if ($this->origin_method == 'put') $this->setVariable($this->getRestInput(new Input));
        else $this->setVariable($_POST);
    }

    private function patchVariable()
    {
        if ($this->origin_method == 'patch') $this->setVariable($this->getRestInput(new Input));
        else $this->setVariable($_POST);
    }

    public function getRestInput(Input $Input)
    {
        return $Input->getInput($this);
    }

    public function __get($name)
    {
        return $this->{$name};
    }
}