<?php
$docRoot = DVELUM_ROOT;
return [
    /*
    * Use foreign keys
    */
    'foreign_keys' => true,
    /*
    * ORM system object used as links storage
    */
    'links_object' => 'Links',
    /*
     * ORM system object used as history storage
     */
    'history_object' => 'Historylog',
    /*
    * ORM system object used as version storage
    */
    'version_object' => 'Vc',
    /*
     * Db_Object for error log
     */
    'error_log_object' => 'error_log',
    /*
     * Log Db_Object errors
     */
    'db_object_error_log' => true,
    'db_object_error_log_path' => $docRoot . '/data/logs/error/db_object.error.log',
    /*
     * Сlear the object version history when deleting an object.
     * The recommended setting is “false”. Thus, even though the object has been deleted,
     * it can be restored from the previous control system revision.
     * If set to "true", the object and its history will be  totally removed. However,
     * this allows you to get rid of redundant records in the database.
     */
    'vc_clear_on_delete' => false,
    /*
     * Write SQL commands when updating Database structure.
     * It can help to determine if there have been performed any rename operations.
     * Please note that renaming operations in ORM interface causes loss of data
     * during server synchronization, so it's better to use SQL log.
     */
    'use_orm_build_log' => true,
    /*
     * ORM SQL logs path
     */
    'log_path' => $docRoot . '/data/logs/orm/',
    /*
     * ORM configs directory
     */
    'object_configs' => 'objects/',
    /*
     * Hard caching time (without validation) for frontend , seconds
     */
    'hard_cache' => 30,
    /*
     * UML Map data file
     */
    'uml_map_data' => 'umlMap.php',
    /*
     * Encrypted object vector field
     */
    'iv_field'=>'enc_iv',
    /*
     * Enable sharding
     */
    'sharding' => true,
    /*
     * Object storage
     */
    'storage' => '\\Dvelum\\Orm\\Record\\Store',
    /*
     * Distributed objects storage
     */
    'distributed_storage' => '\\Dvelum\\Orm\\Distributed\\Record\\Store',
    /*
     * Object translations dir
     */
    'translations_dir' => 'objects/',
    /*
     * Record class
     */
    'record' => '\\Dvelum\\Orm\\Record'
];