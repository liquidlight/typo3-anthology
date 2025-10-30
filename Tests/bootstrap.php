<?php

declare(strict_types=1);

// Bootstrap file for ll_anthology PHPUnit tests

// Define TYPO3 constants for testing
if (!defined('TYPO3_MODE')) {
	define('TYPO3_MODE', 'BE');
}

if (!defined('TYPO3')) {
	define('TYPO3', true);
}

// Set up autoloading for the extension classes
$autoloadFile = __DIR__ . '/../.Build/vendor/autoload.php';
if (file_exists($autoloadFile)) {
	require_once $autoloadFile;
} else {
	// Fallback: try to load from project root
	$projectAutoloadFile = __DIR__ . '/../../../../vendor/autoload.php';
	if (file_exists($projectAutoloadFile)) {
		require_once $projectAutoloadFile;
	}
}


// Set up minimal TYPO3 environment for testing
if (!defined('PATH_site')) {
	define('PATH_site', __DIR__ . '/../.Build/public/');
}

if (!defined('TYPO3_PATH_WEB')) {
	define('TYPO3_PATH_WEB', PATH_site);
}

// Skip TYPO3 Environment initialization for now - too complex for unit tests

// Set up minimal TCA configuration for testing
global $TCA;
if (!isset($TCA)) {
	$TCA = [];
}

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone to avoid warnings
if (!ini_get('date.timezone')) {
	date_default_timezone_set('UTC');
}
