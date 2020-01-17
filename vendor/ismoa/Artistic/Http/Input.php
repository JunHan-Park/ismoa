<?php
namespace Artistic\Http;

use Request;

class Input
{
    private $stream;
    private $match;
    private $deprecated;
    private $input = array();
    private $Request;

    public function __construct()
    {
        $this->stream = file_get_contents('php://input');
    }

    private function parseBoundary()
    {
        $boundary = null;
        if (isset($this->Request->header['content_type'])) {
            if(preg_match('/boundary=(.*)$/', $this->Request->header['content_type'], $matche)) $boundary = $matche[1];
        }
        return $boundary;
    }

    private function splitRaw($boundary)
    {
        $split = preg_split('/-+' . $boundary . '/', $this->stream);
        array_pop($split);
        return $split;
    }

    private function makeMultiArray($path, $data)
    {
        return (!is_null($key = array_pop($path))) ? $this->makeMultiArray($path, array((strlen($key) > 0 ? $key : 0) => $data)) : $data;
    }

    private function isArray($string)
    {
        $this->match = $this->deprecated = array();
        preg_match('/^(.*)\[\]$/i', $string, $this->match);
        preg_match_all('/^\w*|\[(.*?)\]/i', $string, $this->deprecated);
    }

    private function parseFileName($string, $data) {
        $this->isArray($string);
        $file = array();

        if (count($this->match) > 0) {

            $name = $this->deprecated[0][0];
            array_shift($this->deprecated[1]);

            $deprecateds = $this->deprecated[1];
            $variable[$name] = array('name'=>'','type'=>'','tmp_name'=>'', 'error'=>'', 'size'=>'');

            $structure = array('name', 'type', 'tmp_name', 'error', 'size');
            foreach ($structure as $key => $val) {
                if ($key != 0) {
                    array_shift($deprecateds);
                    array_shift($deprecateds);
                }

                array_unshift($deprecateds, $name, $val);

                $make = $deprecateds;
                $make = $this->makeMultiArray($make, $data[$val]);
                $file = array_merge_recursive($file, $make);
            }

        } else {
            $file[$string] = $data;
        }

        $_FILES = array_merge_recursive($_FILES, $file);
    }

    private function parseFile($raw)
    {
        preg_match('/name=\"([^\"]*)\"; filename=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $raw, $match);
        preg_match('/Content-Type: (.*)?/', $match[3], $mime);

        $content = preg_replace('/Content-Type: (.*)[^\n\r]/', '', $match[3]);

        $data = array('name'=>'', 'type' => '', 'tmp_name' => '', 'error' => 4, 'size' => 0);
        if (strlen(trim($content)) > 0) {
            $tmp_name = tempnam(ini_get('upload_tmp_dir'), 'php');
            $error = file_put_contents($tmp_name, ltrim($content));
            register_shutdown_function(function() use($tmp_name) {
                   if (file_exists($tmp_name)) unlink($tmp_name);
                });
            $data = array(
                'name' => $match[2]
                ,'type' => $mime[1]
                ,'tmp_name' => $tmp_name
                ,'error' => (false !== $error) ? 0 : $error
                ,'size' => filesize($tmp_name)
                );
        }
        $this->parseFileName($match[1], $data);
    }

    private function unionArray($path, $data)
    {
        $haskey = false;
        if(!is_null($key = array_pop($path))) {
            $haskey = (strlen($key) > 0) ? true : false;
            $data = $this->makeMultiArray($path, array((strlen($key) > 0 ? $key : 0) => $data));
        }
        if($haskey === true) $this->input = array_replace_recursive($this->input, $data);
        else $this->input = array_merge_recursive($this->input, $data);
    }

    private function parseInputName($string, $data)
    {
        $this->isArray($string);
        $input = array();
        if (count($this->deprecated) > 0) {
            $name = $this->deprecated[0][0];
            array_shift($this->deprecated[1]);
            $deprecated = $this->deprecated[1];
            array_unshift($deprecated, $name);

            $this->unionArray($deprecated, $data);
        } else {
            $input[$string] = $data;
            $this->input = array_merge($this->input, $input);
        }
    }

    private function parseStream($raw)
    {
        preg_match('/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s', $raw, $match);
        $data = (isset($match[2]) && strlen($match[2]) > 0) ? $match[2] : '';

        $this->parseInputName($match[1], $data);
    }

    private function parseBasic($raw)
    {
        preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $raw, $match);

        $data = (isset($match[2]) && strlen($match[2]) > 0) ? $match[2] : '';
        $this->parseInputName($match[1], $data);
    }

    private function confirmInput($split)
    {
        foreach ($split as $key => $raw) {
            if (strlen($raw) < 1) continue;
            if (false !== strpos($raw, 'filename')) {
                $this->parseFile($raw);
            } else {
                if (false !== strpos($raw, 'application/octet-stream')) {
                    $this->parseStream($raw);
                }else{
                    $this->parseBasic($raw);
                }
            }
        }
    }

    private function streamParse()
    {
        $split = preg_split('/\&/', $this->stream);

        $parse = array();
        foreach ($split as $key => $item) {
            list($name, $data) = explode('=', urldecode($item));
            $data = (strlen($data) > 0) ? $data : '';
            $this->parseInputName($name, $data);
        }
        return $parse;
    }

    private function parseInput()
    {
        if (false !== ($boundary = $this->parseBoundary()) && strlen($boundary) < 1) {
            if (is_null($boundary)) $split = $this->streamParse();
            else return parse_str(urlencode($this->stream), $data);
        } else {
            $split = $this->splitRaw($boundary);
            $this->confirmInput($split);
        }
        return $this->input;
    }

    public function getInput(Request $Request)
    {
        $this->Request = $Request;
        return $this->parseInput();
    }
}//end class