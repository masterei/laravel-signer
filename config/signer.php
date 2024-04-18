<?php

return [

    /*
     * The names of the query string parameters that should be ignored
     * during url signature hash validation.
     */
    'ignore_queries' => [
        // 'fbclid',
        // 'utm_campaign',
        // 'utm_content',
        // 'utm_medium',
        // 'utm_source',
        // 'utm_term',
    ],

    /*
     * This is the name of the table that will be created by the migration and
     * used by the package model.
     */
    'table_name' => 'signed_urls',

    /*
     * This is the database connection that will be used by the migration and
     * the package model. In case it's not set; Laravel's default connection
     * will be used instead.
     */
    'database_connection' => env('SIGNER_DB_CONNECTION'),

    /*
     * When the clean-up command is executed, all signed urls older than
     * the number of days specified will be deleted.
     *
     * Note: This feature scheduled to run daily.
     * Tip: Assign zero (0) value to disable this feature.
     */
    'delete_records_older_than_days' => 365,

];
