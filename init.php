<?php // word.php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

//
require __DIR__.'/vendor/autoload.php';

// Configuration
$dbInfo = [
  'driver'    => 'sqlite',
  'host'      => 'localhost',
  'database'  => '/var/www/html/financial/financial.db',
  'username'  => '',
  'password'  => '',
];

// Merge $dbInfo
require __DIR__.'/IlluminateInit.php';

// Autoload eloquent models
spl_autoload_register(function($class){
  global $config;
  if (is_file(__DIR__."/Models/$class".".php"))
    include __DIR__."/Models/$class".".php";
});