<?php

class Response
{

    public function __construct()
    {

    }

    public function encodeJson(array $data)
    {
        header('Content-type: application/json');
        return json_encode($array);
    }

    public function httpCode($code)
    {
        return http_response_code($code);
    }
}