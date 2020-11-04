<?php

// Retrieve instance of the framework
require(__DIR__.'/../vendor/autoload.php');
$f3 = Base::instance();

// Initialize CMS
$f3->config(__DIR__.'/../app/config.ini');

// Define routes
$f3->config(__DIR__.'/../app/routes.ini');

// Execute application
$f3->run();
