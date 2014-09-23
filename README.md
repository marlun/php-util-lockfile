# php-util-lockfile

Handles limiting running instances of a script

## Usage

	Problem\Util\LockFile::test($options);

## Options

### path
**Type:** String<br>
**Default:** `__DIR__ . '/LOCK_FILE'  `<br>
Path to where the lock file will be written<br>

### maxCount
**Type:** Number<br>
**Default:** 1<br>
Maximum allowed parallel executions of the script<br>
