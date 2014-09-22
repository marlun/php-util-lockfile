<?php

namespace Problem\Util;

function lockfile($options = array()) {
	// The name of lock file which will be created
	$path = isset($options['path'])
		? $options['path']
		: __DIR__ . '/LOCK_FILE';
	if (trim($path) === '') {
		throw new \InvalidArgumentException('Lock file path is empty');
	}

	// The number of allowed parallel executions
	$maxCount = isset($options['maxCount'])
		? $options['maxCount']
		: 1;
	if (!is_numeric($maxCount)) {
		throw new \InvalidArgumentException('Lock file max count is not numeric');
	}

	// If this is the first execution of the script we create the lock file
	// and add 1 to it.
	if (!file_exists($path)) {
		$created = touch($path);
		if (!$created) {
			throw new \Exception("Couldn't create lock file");
		}
		return file_put_contents($path, 1);
	}


	// Read in the current count from the lock file and add 1 to it unless
	// we've met the maximum allowed count.
	$count = file_get_contents($path);
	if (!is_numeric($count)) {
		throw new \UnexpectedValueException('Lock file count is not numeric');
	}

	$newCount = $count + 1;
	if ($newCount > $maxCount) {
		throw new \Exception('Maximum allowed parallel executions');
	}
	file_put_contents($path, $newCount);

	// Register a function which is run when the execution of the script
	// stops. The function checks the value of the lock file and either
	// decreases the count or removes the file if the count is 1.
	if (!defined('LOCK_SHUTDOWN_FUNCTION')) {
		define('LOCK_SHUTDOWN_FUNCTION', true);
		register_shutdown_function(function($path) {
			$count = file_get_contents($path);
			if ($count <= 1) {
				unlink($path);
			} else {
				file_put_contents($path, $count - 1);
			}
		}, $path);
	}
}
