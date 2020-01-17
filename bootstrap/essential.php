<?php
register_shutdown_function(function(){
    $detail = error_get_last();
    if (is_array($detail) && count($detail)) {
        $data = [];
        $config = Env::getConfig();
        $data['develop'] = isset($config['develop']) ? $config['develop'] : true;
        $errors = [
            E_ERROR => 'ERROR'
            ,E_WARNING => 'WARNING'
            ,E_PARSE => 'PARSE'
            ,E_NOTICE => 'NOTICE'
            ,E_CORE_ERROR => 'CORE_ERROR'
            ,E_CORE_WARNING => 'CORE_WARNING'
            ,E_COMPILE_ERROR => 'COMPILE_ERROR'
            ,E_COMPILE_WARNING => 'COMPILE_WARNING'
            ,E_USER_ERROR => 'USER_ERROR'
            ,E_USER_WARNING => 'USER_WARNING'
            ,E_USER_NOTICE => 'USER_NOTICE'
            ,E_STRICT => 'STRICT'
            ,E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR.'
            ,E_DEPRECATED => 'DEPRECATED'
            ,E_USER_DEPRECATED => 'USER_DEPRECATED'
        ];

        $data['msg'] = $errors[$detail['type']];
        $data['code'] = $detail['type'];
        $data['file'] = $detail['file'];
        $data['line'] = $detail['line'];
        $data['string'] = $detail['message'];

        Log::error(array($errors[$detail['type']], $detail['message']));
        extract($data);
        die(require_once(__DIR__ . '/../vendor/ismoa/Assist/error/exception.html'));
    }
});

set_error_handler(function($code, $message, $file, $line){
    $data = [];
    $config = Env::getConfig();

    $data['develop'] = isset($config['develop']) ? $config['develop'] : true;
    $data['msg'] = $message;
    $data['code'] = $code;
    $data['file'] = $file;
    $data['line'] = $line;
    $data['string'] = $message;

    $errors = [
            E_ERROR => 'ERROR'
            ,E_WARNING => 'WARNING'
            ,E_PARSE => 'PARSE'
            ,E_NOTICE => 'NOTICE'
            ,E_CORE_ERROR => 'CORE_ERROR'
            ,E_CORE_WARNING => 'CORE_WARNING'
            ,E_COMPILE_ERROR => 'COMPILE_ERROR'
            ,E_COMPILE_WARNING => 'COMPILE_WARNING'
            ,E_USER_ERROR => 'USER_ERROR'
            ,E_USER_WARNING => 'USER_WARNING'
            ,E_USER_NOTICE => 'USER_NOTICE'
            ,E_STRICT => 'STRICT'
            ,E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR.'
            ,E_DEPRECATED => 'DEPRECATED'
            ,E_USER_DEPRECATED => 'USER_DEPRECATED'
        ];

    $msg = $message . ' in ' . $file . ':' . $line;
    Log::error(array($errors[$code], $msg));
    print($msg);
});