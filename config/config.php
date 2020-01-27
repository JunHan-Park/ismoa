<?php

return array(
    // is develop
    'develop' => true

    //csrf token sets the effective time.
    ,'csrf_ttl' => 10

    //allow http method 
    ,'method' => array('put', 'patch', 'delete')

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
