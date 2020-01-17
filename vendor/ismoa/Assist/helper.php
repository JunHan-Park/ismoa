<?php
function redirect($uri = '/', $qrst = array())
{
    return Helper::getInstance()->redirect($uri, $qrst);
}

function back($uri = '', $qrst = array())
{
    return Helper::getInstance()->back($uri, $qrst);
}

function withMsg($msg)
{
    return Helper::getInstance()->withMsg($msg);
}

function view($url, $data = array())
{
    return View::getInstance()->renderView($url, $data);
}

function csrf_token()
{
    return Helper::getInstance()->getCsrfToken();
}

function trans($package, $parameter = array())
{
    return Translator::getInstance()->activeTranslate($package, $parameter);
}

function config($key)
{
    return Helper::getInstance()->getConfig($key);
}

function past($key)
{
    return Helper::getInstance()->pastInput($key);
}

function isUrl($pattern)
{
    if (strlen($pattern) < 1) return false;
    $url = Request::getInstance()->url;
    if (false !== strpos($pattern, '*')) $pattern = str_replace('*', '[\w|\.?|\/]*', $pattern);
    return (bool)preg_match('#^'.$pattern.'$#u', $url);
}

function paginate($tcount, $lcount = 10, $bcount = 3, $querystring = array())
{
    return Paginate::getInstance()->paginate($tcount, $lcount, $bcount, $querystring);
}