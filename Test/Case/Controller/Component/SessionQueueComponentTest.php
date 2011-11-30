<?php

App::uses('NinjaComponentTestCase', 'Ninja.TestSuite');

class SessionQueueComponentTestCase extends NinjaComponentTestCase {

	protected $_backup;

	public function setUp() {
		parent::setUp();
		$this->_backup = isset($_SESSION) ? $_SESSION : null;
		$this->SessionQueue->Session->delete('SessionQueue');
	}

	public function tearDown() {
		parent::tearDown();
		if ($this->_backup !== null) {
			$_SESSION = $this->_backup;
			$this->_backup = null;
		}
	}

	public function testBasics() {
		$this->assertIdentical($this->SessionQueue->count(), 0);
		$this->assertTrue($this->SessionQueue->delete(0));
		$this->assertTrue($this->SessionQueue->clear());
		$this->assertTrue($this->SessionQueue->purge());

		$this->assertIdentical($this->SessionQueue->read(), array());

		$this->assertTrue($this->SessionQueue->write(array(1, 2)));
		$this->assertIdentical($this->SessionQueue->count(), 2);
		$this->assertIdentical($this->SessionQueue->read(), array(1, 2));
		$this->assertIdentical($this->SessionQueue->read(1), 2);

		$this->assertTrue($this->SessionQueue->delete(0));
		$this->assertIdentical($this->SessionQueue->count(), 1);
		$this->assertIdentical($this->SessionQueue->read(1), 2);
		$this->assertIdentical($this->SessionQueue->read(0), null);

		$this->assertTrue($this->SessionQueue->write(null));
		$this->assertIdentical($this->SessionQueue->count(), 0);
		$this->assertIdentical($this->SessionQueue->read(), array());

		$data = array(
			array(1),
			array(2),
		);
		$this->assertTrue($this->SessionQueue->write($data));
		$this->assertIdentical($this->SessionQueue->count(), 2);
		$this->assertIdentical($this->SessionQueue->read(), $data);
		$this->assertIdentical($this->SessionQueue->read(1), array(2));

		$this->assertTrue($this->SessionQueue->clear());
		$this->assertIdentical($this->SessionQueue->count(), 0);
		$this->assertIdentical($this->SessionQueue->read(), array());

		$this->assertTrue($this->SessionQueue->write($data));
		$this->assertTrue($this->SessionQueue->purge());
		$this->assertIdentical($this->SessionQueue->count(), 0);
		$this->assertIdentical($this->SessionQueue->read(), array());

	}

	public function testActsAsArray() {
		$this->assertIdentical(count($this->SessionQueue), 0);

		$this->SessionQueue[] = 'hoge';
		$this->assertIdentical($this->SessionQueue[0], 'hoge');

		$this->SessionQueue[0] = 'fuga';
		$this->SessionQueue[] = 'hoge';
		$this->assertIdentical($this->SessionQueue[0], 'fuga');
		$this->assertIdentical($this->SessionQueue[1], 'hoge');
		$this->assertIdentical(count($this->SessionQueue), 2);

		$iterated = array();
		foreach ($this->SessionQueue as $key => $val) {
			$iterated[$key] = $val;
		}
		$this->assertIdentical($iterated, array('fuga', 'hoge'));

		$this->assertTrue(isset($this->SessionQueue[0]));
		unset($this->SessionQueue[0]);
		$this->assertFalse(isset($this->SessionQueue[0]));
	}

	public function testConveniences() {
		$this->assertTrue($this->SessionQueue->isEmpty());

		$this->SessionQueue->push('hoge');
		$this->assertFalse($this->SessionQueue->isEmpty());

		$this->assertIdentical($this->SessionQueue->pop(), 'hoge');
		$this->assertTrue($this->SessionQueue->isEmpty());

		$this->SessionQueue->push('piyo');
		$this->SessionQueue->unshift('fuga');
		$this->assertFalse($this->SessionQueue->isEmpty());
		$this->assertIdentical($this->SessionQueue->shift(), 'fuga');
	}

	public function testSearch() {

		$this->SessionQueue->write(array(
			array('name' => 'hoge'),
			array('name' => 'fuga'),
			array('name' => 'fuga'),
		));
		$this->assertFalse($this->SessionQueue->search(array('name' => 'piyo')));
		$this->assertIdentical($this->SessionQueue->search(array('name' => 'fuga')), 1);
		$this->assertIdentical($this->SessionQueue->search(array('name' => 'fuga'), array('multiple' => true)), array(1, 2));

		$this->SessionQueue->write(array(
			1,
			2,
			2,
		));
		$this->assertFalse($this->SessionQueue->search('1', array('strict' => true)));
		$this->assertIdentical($this->SessionQueue->search(2, array('strict' => true)), 1);
		$this->assertIdentical($this->SessionQueue->search(2, array('strict' => true, 'multiple' => true)), array(1, 2));
	}

}
