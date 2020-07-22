<?php
return [

    /**
     *  Enable query log
     */
    'enabled' => env('QUERY_LOG_ENABLED', false),

    /**
     * Log location of file
     */
    'log_location' => env('QUERY_LOG_FILE', storage_path() . '/logs/sql.log'),

    /**
     *  file_get_contents flags for log file
     *  Possibilities:
     *  FILE_USE_INCLUDE_PATH
     *  FILE_APPEND
     *  LOCK_EX
     */
    'log_file_type' => env('QUERY_LOG_FILE_FLAGS', FILE_APPEND),

];

