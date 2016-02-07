#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/product_info.php';
require __DIR__ . '/product_data.php';

use \product_info\Products;
use Symfony\Component\Console\Application;


$application = new Application();
$application->add(new Products());
$application->run();