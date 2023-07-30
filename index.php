<?php

require_once __DIR__ . '/vendor/autoload.php';

require 'config.php';
require 'examples/classy/JokesController.php';

use Zarf\Zarf;

$app = new Zarf;

$app->register('/', function () {
    echo "Welcome to Zarf-php";
});

$app->controller('/jokes', JokesController::class);

$app->run();
