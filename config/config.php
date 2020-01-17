<?php

return array(
    // is develop
    'develop' => true

    //csrf token sets the effective time.
    ,'csrf_ttl' => 10

    //allow http method 
    ,'method' => array('put', 'patch', 'delete')

    // database account infomation
    ,'database' => array(
        'host'      => 'localhost'
        ,'port'     => '3306'
        ,'dbname'   => 'database name'
        ,'username'  => 'database user name'
        ,'passwd'   => 'databse password'
        ,'charset'  => 'utf8'
    )
    //default locale
    ,'locale' => 'kr'
    //pagenate setting
    ,'pagenate' => array(
        'first_page'    => true
        ,'prev_block'   => true
        ,'prev_page'    => true
        ,'next_page'    => true
        ,'next_block'   => true
        ,'last_page'    => true
    )
);