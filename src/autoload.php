<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
chdir($_SERVER['DOCUMENT_ROOT'] . '/..');

if (strpos($_SERVER['HTTP_USER_AGENT'], '(DEBUG)')) {
    define('DEBUGGING', true);
}

require __DIR__ . '/Support/helpers.php';
require 'vendor/autoload.php';