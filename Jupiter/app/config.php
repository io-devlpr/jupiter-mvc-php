<?php

/*
 * This contains all of the apache configurations that are going to be implemented in the application
 */

return [

    'error_handling' => 'no',

    'development_environment' => true,

    'language' => 'en',

    'user_scope' => [

        'admin' => '\ScopeAccessible\Admin::class'

    ],

    'datetime' => 'Africa/Dar_es_Salaam',

    'err_log_file' => 'sys_error.log'
];