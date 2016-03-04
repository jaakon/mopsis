<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
chdir('..');

require __DIR__ . '/Support/helpers.php';
/**
 * @noinspection PhpIncludeInspection
 */
require 'vendor/autoload.php';

(new \Mopsis\Core\Bootstrap())->kickstart($_GET['flush']);
