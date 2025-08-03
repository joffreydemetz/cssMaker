<?php

/**
 * PHPUnit Bootstrap file for CssMaker tests
 */

// Ensure we have autoloading
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die('Composer autoload file not found. Please run "composer install" first.');
}

require_once __DIR__ . '/../vendor/autoload.php';

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone for consistent test results
date_default_timezone_set('UTC');

// Define test constants if needed
define('CSSMAKER_TEST_MODE', true);

// Ensure temp directory exists and is writable
$tempDir = sys_get_temp_dir();
if (!is_writable($tempDir)) {
    die('Temporary directory is not writable: ' . $tempDir);
}

echo "CssMaker Test Bootstrap loaded successfully\n";
