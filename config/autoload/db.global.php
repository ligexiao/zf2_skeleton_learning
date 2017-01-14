<?php
/**
 * Global configure for database config in production evn
 * 
 * Don't change this configure in dev or test env, if you wan't change, see db.local.php
 */
if (ENV == 'production') {
    return array(
        'dbconfig' => array(
            'test' => array(
                'driver' => 'Pdo_Mysql',
                'database' => 'test',
                'username' => 'root',
                'password' => '',
                'hostname' => '127.0.0.1',
                'port' => '3306',
                'charset' => 'utf8'
            )
        )
    );
} else {
    return array();
}