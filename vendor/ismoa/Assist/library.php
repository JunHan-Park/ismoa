<?php
function _pr($item)
{
    echo '<pre>';
    print_r($item);
    echo '</pre>';
}

function getPostMaxSize()
{
    return ini_get('post_max_size');
}

function convByte($str)
{
    $units = array('k' => 1024, 'm' => 1024 * 1024, 'g' => 1024 * 1024 * 1024);
    preg_match('/^(?<volume>\d+)(?<unit>[K|M|G]*)$/i', $str, $match);
    $volume = $match['volume'];
    $unit = strtolower($match['unit']);

    return $volume * $units[$unit];
}

function sha256($str)
{
    return hash('sha256', $str);
}

function listMime(array $need)
{
    $mimes = array();
    $mimes['image'] = array('image/gif', 'image/jpeg', 'image/png');
    $mimes['office'] = array(
    'application/vnd.ms-excel'
    ,'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ,'application/vnd.ms-powerpoint'
    ,'application/vnd.openxmlformats-officedocument.presentationml.presentation'
    ,'application/msword'
    ,'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ,'application/vnd.openxmlformats-officedocument.wordprocessingml.template'
    );
    $mimes['text'] = array('text/plain', 'text/csv');
    $mimes['hangul'] = array('application/haansofthwp','application/vnd.hancom.hwpx');
    $mimes['video'] = array('video/mp4 ','video/mpeg', 'video/x-ms-wmv','video/x-msvideo');  
    $mimes['zip'] = array('application/zip');

    $mime = array();
    if (count($need) > 0) {
        foreach($need as $key => $type) {
            if (isset($mimes[$type])) $mime = array_merge($mime, $mimes[$type]);
        }
    }

    return $mime;
}

function uploadErrorMsg($int)
{   
    $error = array(
            0 => '업로드 정상' //UPLOAD_ERR_OK
            ,1 => 'PHP에 설정된 최대 파일 크기 초과' //UPLOAD_ERR_INI_SIZE
            ,2 => 'HTML 폼에 설정된 최대 파일크기 초과'//UPLOAD_ERR_FORM_SIZE
            ,3 => '파일의 일부만 업로드 됨' //UPLOAD_ERR_PARTIAL
            ,4 => '업로드할 파일이 없음' //UPLOAD_ERR_NO_FILE
            ,6 => '웹서버에 임시폴더가 없음' //UPLOAD_ERR_NO_TMP_DIR
            ,7 => '웹서버에 파일을 쓸 수 없음' //UPLOAD_ERR_CANT_WRITE
            ,8 => 'POP 확장기능에 의한 업로드 중단' //UPLOAD_ERR_EXTENSION
        );

    if (strlen($int) > 0) {
        return $error[$int];
    }
}

function error($msg, $code, $file = '', $line, $string)
{
    $error['develop'] = isDevelopMode();
    $error['msg'] = $msg;
    $error['code'] = ($code != 404)? 500 : 404;
    $error['file'] = $file;
    $error['line'] = $line;
    $error['string'] = $string;

    http_response_code($error['code']);
    $view = route('error').'/'.$error['code'].'.html';
    extract($error);
    die(require_once($view));
}

function encrypt($str)
{
    global $_INI;
    $key = hash('sha256', $_INI['app']['key']);
    $iv = substr(hash('sha256', $_INI['app']['iv']), 0, 16);

    return str_replace("=", "", base64_encode(openssl_encrypt($str, "AES-256-CBC", $key, 0, $iv)));
}

function decrypt($str)
{
    global $_INI;
    $key = hash('sha256', $_INI['app']['key']);
    $iv = substr(hash('sha256', $_INI['app']['iv']), 0, 16);

    return openssl_decrypt(base64_decode($str), "AES-256-CBC", $key, 0, $iv);
}

function parseArrayDepth(array $arr)
{
    $depth = 1;
    foreach ($arr as $key => $val) {
        if (is_array($val)) {
            $depth += parseArrayDepth($val);
            break;
        }
    }
    return $depth;
}

function isMsg()
{
    return (isset($_SESSION['msg']) && strlen($_SESSION['msg']) > 0) ? true : false;
}

function parseAgent()
{
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $browser = array(
            'MSIE' => 'Internet Explorer'
            ,'rv' => 'Internet Explorer'
            ,'Edge' => 'Microsoft Edge'
            ,'Firefox' => 'Mozilla Firefox'
            ,'Chrome' => 'Google Chrome'
            ,'Safari' => 'Apple Safari'
            ,'Opera' => 'Opera'
            ,'Netscape' => 'Netscape'
            ,'unknown' => 'unknown'
            );

    if (preg_match('/linux/i', $agent)) $platform = 'Linux';
    elseif (preg_match('/macintosh|mac os x/i', $agent)) $platform = 'Mac';
    elseif (preg_match('/Windows NT 10.0/i', $agent)) $platform = 'Windows 10';
    elseif (preg_match('/windows|win32/i', $agent)) $platform = 'Windows';
    else $platform = 'unknown';

    if (preg_match('/MSIE/i',$agent) && !preg_match('/Opera/i',$agent)) $nick = 'MSIE'; 
    elseif (preg_match('/Edge/i',$agent)) $nick = 'Edge';
    elseif (preg_match('/Firefox/i',$agent)) $nick = "Firefox"; 
    elseif (preg_match('/Chrome/i',$agent)) $nick = "Chrome"; 
    elseif (preg_match('/Safari/i',$agent)) $nick = "Safari"; 
    elseif (preg_match('/Opera/i',$agent)) $nick = 'Opera'; 
    elseif(preg_match('/Netscape/i',$agent)) $nick = 'Netscape'; 
    else {
        if(preg_match('/Trident*/i',$agent) && preg_match('/rv:11*/i',$agent) && preg_match('/Trident*/i',$agent)) $nick = 'rv';
        else $nick = 'unknown';
    }

    $separator = ($nick == 'rv') ? ':': '/';
    $pattern = '#(?<browser>Version|' . $nick . '|other)[' . $separator . ' ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $agent, $matche)) $version = -1;
    else {
        if (1 != count($matche)){
            if (strripos($agent, "Version") < strripos($agent, $nick)) $version = $matche['version'][0];
            else $version= $matche['version'][1];
        } else {
            $version = $matche['version'][0];
        }
    }

    return array(
        'agent' => $agent
        ,'platform' => $platform
        ,'browser' => $browser[$nick]
        ,'version' => $version
        );
}

function isMobile()
{
    $regex = '/(iPod|iPhone|Android|BlackBerry|SymbianOS|SCH-M\d+|Opera Mini|Windows CE|Nokia|SonyEricsson|webOS|PalmOS)/i';

    return (preg_match($regex, $_SERVER['HTTP_USER_AGENT']))? true : false;
}

function resJson($msg, $err = true, $option = JSON_FORCE_OBJECT)
{
    $json['err'] = $err;
    if ($err !== false) $json['msg'] = $msg;
    return json_encode($json, $option);
}
