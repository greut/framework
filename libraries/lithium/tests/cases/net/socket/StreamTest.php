<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace lithium\tests\cases\net\socket;

use lithium\net\http\Request;
use lithium\net\http\Response;
use lithium\net\socket\Stream;

class StreamTest extends \lithium\test\Unit {

	protected $_testConfig = array(
		'persistent' => false,
		'scheme' => 'http',
		'host' => 'example.org',
		'port' => 80,
		'timeout' => 2,
		'classes' => array('request' => 'lithium\net\http\Request')
	);

	public function skip() {
		$config = $this->_testConfig;
		$url = "{$config['scheme']}://{$config['host']}";
		$message = "Could not open {$url} - skipping " . __CLASS__;
		$this->skipIf(!fopen($url, 'r'), $message);
	}

	public function testAllMethodsNoConnection() {
		$stream = new Stream(array('scheme' => null));
		$this->assertFalse($stream->open());
		$this->assertTrue($stream->close());
		$this->assertFalse($stream->timeout(2));
		$this->assertFalse($stream->encoding('UTF-8'));
		$this->assertFalse($stream->write(null));
		$this->assertFalse($stream->read());
		$this->assertTrue($stream->eof());
		$this->assertNull($stream->send(new Request()));
	}

	public function testOpen() {
		$stream = new Stream($this->_testConfig);
		$result = $stream->open();
		$this->assertTrue($result);

		$result = $stream->resource();
		$this->assertTrue(is_resource($result));
	}

	public function testClose() {
		$stream = new Stream($this->_testConfig);
		$result = $stream->open();
		$this->assertTrue($result);

		$result = $stream->close();
		$this->assertTrue($result);

		$result = $stream->resource();
		$this->assertFalse(is_resource($result));
	}

	public function testTimeout() {
		$stream = new Stream($this->_testConfig);
		$result = $stream->open();
		$stream->timeout(10);
		$result = $stream->resource();
		$this->assertTrue(is_resource($result));
	}

	public function testEncoding() {
		$stream = new Stream($this->_testConfig);
		$result = $stream->open();
		$stream->encoding('UTF-8');
		$result = $stream->resource();
		$this->assertTrue(is_resource($result));

		$stream = new Stream($this->_testConfig + array('encoding' => 'UTF-8'));
		$result = $stream->open();
		$result = $stream->resource();
		$this->assertTrue(is_resource($result));
	}

	public function testWriteAndRead() {
		$stream = new Stream($this->_testConfig);
		$this->assertTrue(is_resource($stream->open()));
		$this->assertTrue(is_resource($stream->resource()));

		$result = $stream->write();
		$this->assertTrue($result == 82 || $result == 84);
		$this->assertPattern("/^HTTP/", (string) $stream->read());
	}

	public function testSendWithNull() {
		$stream = new Stream($this->_testConfig);
		$this->assertTrue(is_resource($stream->open()));
		$result = $stream->send(
			new Request($this->_testConfig),
			array('response' => 'lithium\net\http\Response')
		);
		$this->assertTrue($result instanceof Response);
		$this->assertPattern("/^HTTP/", (string) $result);
		$this->assertTrue($stream->eof());
	}

	public function testSendWithArray() {
		$stream = new Stream($this->_testConfig);
		$this->assertTrue(is_resource($stream->open()));
		$result = $stream->send($this->_testConfig,
			array('response' => 'lithium\net\http\Response')
		);
		$this->assertTrue($result instanceof Response);
		$this->assertPattern("/^HTTP/", (string) $result);
		$this->assertTrue($stream->eof());
	}

	public function testSendWithObject() {
		$stream = new Stream($this->_testConfig);
		$this->assertTrue(is_resource($stream->open()));
		$result = $stream->send(
			new Request($this->_testConfig),
			array('response' => 'lithium\net\http\Response')
		);
		$this->assertTrue($result instanceof Response);
		$this->assertPattern("/^HTTP/", (string) $result);
		$this->assertTrue($stream->eof());
	}
}

?>