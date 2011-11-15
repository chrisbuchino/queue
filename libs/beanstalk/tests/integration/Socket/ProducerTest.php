<?php

require_once 'Socket/Beanstalk.php';

class ProducerTest extends PHPUnit_Framework_TestCase {

	public $subject;

	protected function setUp() {
		$this->subject = new Socket_Beanstalk(array(
			'host' => TEST_SERVER_HOST,
			'port' => TEST_SERVER_PORT
		));
		if (!$this->subject->connect()) {
			$message = 'Need a running beanstalk server at ' . TEST_SERVER_HOST . ':' . TEST_SERVER_PORT;
			$this->markTestSkipped($message);
		}

		foreach ($this->subject->listTubes() as $tube) {
			$this->subject->choose($tube);

			while ($job = $this->subject->peekReady()) {
				$this->subject->delete($job['id']);
			}
			while ($job = $this->subject->peekBuried()) {
				$this->subject->delete($job['id']);
			}
		}
	}

	public function testPut() {
		$result = $this->subject->put(0, 0, 100, 'test');
		$this->assertGreaterThan(1, $result);
	}

	public function testChoose() {
		$result = $this->subject->choose('test0');
		$this->assertEquals('test0', $result);

		$result = $this->subject->choose('test1');
		$this->assertEquals('test1', $result);
	}

	public function testReserveWithoutTimeout() {
		$this->subject->put(0, 0, 100, 'test');

		$result = $this->subject->reserve();
		$this->assertEquals('test0', $result);
	}
}

?>