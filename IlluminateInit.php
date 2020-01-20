<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

// Pass database information
$capsule->addConnection(array_merge([
    'driver'    => 'sqlite',
    'host'      => '',
    'database'  => '',
    'username'  => '',
    'password'  => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
    'options'   => array(
        // PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
    ),
], $dbInfo));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();