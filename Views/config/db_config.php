<?php
return [
    'driver' => 'mysql',
    'host' => MYSQLHOST,
    'dbname' => DATABASE,
    'username' => 'root',
    'password' => '12345678',
    'charset' => 'utf8mb4',
    'access' => [0,1,9], // Список  из user access, кто может заходить под этим пользователем
];