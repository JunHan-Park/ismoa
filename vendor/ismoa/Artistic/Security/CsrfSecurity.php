<?php
namespace Artistic\Security;

use Request;

class CsrfSecurity
{
    private $ttl;
    private $token;
    private $host;
    private $referer;
    private $csrf_token;
    private $sess;

    use \Artistic\Traits\Singleton;

    public function __construct()
    {
        $this->token = null;
        $this->sess = array('token' => '','time' => 0, 'uri' => '');
        $this->setConfig();
    }

    private function setConfig()
    {
        $ttl = config('config.csrf_ttl');
        $this->ttl = (strlen($ttl) > 0 && $ttl > 0) ? $ttl : 10;
    }

    private function makeToken(Request $Request)
    {
        $uniqid = isset($Request->header['unique_id']) ? $Request->header['unique_id'] : uniqid();
        $this->token = md5($Request->header['remote_addr'] 
                        . $Request->header['request_uri']
                        . $Request->header['http_user_agent']
                        . $uniqid 
                        . time()
                        );
    }

    private function parseField(Request $Request)
    {
        $referer = isset($Request->header['referer']) ? $Request->header['referer'] : '';
        $http_referer = isset($Request->header['http_referer']) ? $Request->header['http_referer'] : '';
        $this->host = isset($Request->header['http_host']) ? $Request->header['http_host'] : '';

        $csrf_token = isset($Request->variable[$Request->method]['_token']) 
                        ? $Request->variable[$Request->method]['_token'] : '';

        $x_csrf_token = isset($Request->header['x-csrf-token']) ? $Request->header['x-csrf-token'] : '';
        $this->sess['token']    = isset($_SESSION['artistic']['csrf']['token']) ? $_SESSION['artistic']['csrf']['token'] : '';
        $this->sess['time']     = isset($_SESSION['artistic']['csrf']['time']) ? $_SESSION['artistic']['csrf']['time'] : '';
        $this->sess['uri']      = isset($_SESSION['artistic']['csrf']['uri']) ? $_SESSION['artistic']['csrf']['uri'] : '';

        $csrf_token = (!$csrf_token) ? $x_csrf_token : $csrf_token;
        $referer = (!$referer) ? $http_referer : $referer;

        $this->csrf_token = $csrf_token;
        $this->referer = (!empty($referer)) ? parse_url($referer) : array();
    }

    private function verifyHost()
    {
        return (isset($this->referer['host']) && $this->referer['host'] == $this->host) ? true : false;
    }

    private function verifyReferer()
    {
        return (isset($this->referer['path']) && $this->referer['path'] == $this->sess['uri']) ? true : false;
    }

    private function isCsrfField()
    {
        return (strlen($this->csrf_token) > 0) ? true : false;
    }

    private function isLiveToken()
    {
        $limit = 60 * $this->ttl;
        $time = $this->sess['time'] + $limit;
        return ($time >= time()) ? true : false;
    }

    private function verifyToken()
    {
        return (strlen($this->sess['token']) == 32 && $this->sess['token'] == $this->csrf_token) ? true : false;
    }

    public function verifyCsrf(Request $Request)
    {
        $this->parseField($Request);
        if (true !== $this->verifyHost()) throw new \ArtisticException('Host does not match.', 500);
        if (true !== $this->isCsrfField()) throw new \ArtisticException('Token field in not found', 500);
        if (true !== $this->isLiveToken()) throw new \ArtisticException('Token valid timeout.', 500);
        if (true !== $this->verifyToken()) throw new \ArtisticException('Token does not match.', 500);
    }

    public function getToken(Request $Request)
    {
        $this->makeToken($Request);

        if (isset($_SESSION['artistic']['csrf'])) unset($_SESSION['artistic']['csrf']);
        $_SESSION['artistic']['csrf'] = array('token' =>$this->token, 'time' => time(), 'uri' => $Request->header['request_uri']);
        return $this->token;
    }

}//end class