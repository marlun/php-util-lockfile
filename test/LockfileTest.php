<?php

require 'vendor/autoload.php';
require 'lockfile.php';

use org\bovigo\vfs\vfsStream;

class LockfileTests extends PHPUnit_Framework_TestCase {

	function setUp() {
		$this->root = vfsStream::setup('root');
		$this->path = vfsStream::url('root') . '/LOCK_FILE';
	}

	function testSendingInBadFilename() {
		$this->setExpectedException('InvalidArgumentException',
			'Lock file path is empty');
		Problem\Util\LockFile::test(array('path' => ' '));
	}

	function testSendingInBadMaxCount() {
		$this->setExpectedException('InvalidArgumentException',
			'Lock file max count is not numeric');
		Problem\Util\LockFile::test(array('maxCount' => 'r'));
	}

	function testThatLockFileIsCreated() {
		$this->assertFalse($this->root->hasChild('LOCK_FILE'));
		Problem\Util\LockFile::test(array('path' => $this->path));
		$this->assertTrue($this->root->hasChild('LOCK_FILE'));
	}

	function testThatCounterDefaultsToOne() {
		$this->setExpectedException('Exception',
			'Maximum allowed parallel executions');
		Problem\Util\LockFile::test(array('path' => $this->path));
		Problem\Util\LockFile::test(array('path' => $this->path));
	}

	function testThatCounterWorks() {
		Problem\Util\LockFile::test(array('path' => $this->path, 'maxCount' => 3));
		$this->assertEquals(1, file_get_contents($this->path));
		Problem\Util\LockFile::test(array('path' => $this->path, 'maxCount' => 3));
		$this->assertEquals(2, file_get_contents($this->path));
		Problem\Util\LockFile::test(array('path' => $this->path, 'maxCount' => 3));
		$this->assertEquals(3, file_get_contents($this->path));
	}

	function testThatMaxCountWorks() {
		$this->setExpectedException('Exception',
			'Maximum allowed parallel executions');
		Problem\Util\LockFile::test(array('path' => $this->path, 'maxCount' => 3));
		Problem\Util\LockFile::test(array('path' => $this->path, 'maxCount' => 3));
		Problem\Util\LockFile::test(array('path' => $this->path, 'maxCount' => 3));
		Problem\Util\LockFile::test(array('path' => $this->path, 'maxCount' => 3));
	}

	function testInvalidCountValue() {
		$this->setExpectedException('UnexpectedValueException',
			'Lock file count is not numeric');
		Problem\Util\LockFile::test(array('path' => $this->path, 'maxCount' => 3));
		file_put_contents($this->path, 'a');
		Problem\Util\LockFile::test(array('path' => $this->path, 'maxCount' => 3));
	}

}
