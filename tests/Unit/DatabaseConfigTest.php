<?php

test('database config uses the PHP 8.5 mysql ssl ca constant', function () {
    $databaseConfig = file_get_contents(dirname(__DIR__, 2).'/config/database.php');

    expect($databaseConfig)->toContain('use Pdo\\Mysql;');
    expect($databaseConfig)->toContain('Mysql::ATTR_SSL_CA');
    expect($databaseConfig)->not->toContain('PDO::MYSQL_ATTR_SSL_CA');
});