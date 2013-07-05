<?php

App::uses('NinjaComponentTestCase', 'Ninja.TestSuite');

class SessionQueueComponentTest extends NinjaComponentTestCase {

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
		$this->assertSame(0, $this->SessionQueue->count());
		$this->assertTrue($this->SessionQueue->delete(0));
		$this->assertTrue($this->SessionQueue->clear());
		$this->assertTrue($this->SessionQueue->purge());

		$this->assertSame(array(), $this->SessionQueue->read());

		$this->assertTrue($this->SessionQueue->write(array(1, 2)));
		$this->assertSame(2, $this->SessionQueue->count());
		$this->assertSame(array(1, 2), $this->SessionQueue->read());
		$this->assertSame(2, $this->SessionQueue->read(1));

		$this->assertTrue($this->SessionQueue->delete(0));
		$this->assertSame(1, $this->SessionQueue->count());
		$this->assertSame(2, $this->SessionQueue->read(1));
		$this->assertSame(null, $this->SessionQueue->read(0));

		$this->assertTrue($this->SessionQueue->write(null));
		$this->assertSame(0, $this->SessionQueue->count());
		$this->assertSame(array(), $this->SessionQueue->read());

		$data = array(
			array(1),
			array(2),
		);
		$this->assertTrue($this->SessionQueue->write($data));
		$this->assertSame(2, $this->SessionQueue->count());
		$this->assertSame($data, $this->SessionQueue->read());
		$this->assertSame(array(2), $this->SessionQueue->read(1));

		$this->assertTrue($this->SessionQueue->clear());
		$this->assertSame(0, $this->SessionQueue->count());
		$this->assertSame(array(), $this->SessionQueue->read());

		$this->assertTrue($this->SessionQueue->write($data));
		$this->assertTrue($this->SessionQueue->purge());
		$this->assertSame(0, $this->SessionQueue->count());
		$this->assertSame(array(), $this->SessionQueue->read());

	}

	public function testActsAsArray() {
		$this->assertSame(0, count($this->SessionQueue));

		$this->SessionQueue[] = 'hoge';
		$this->assertSame('hoge', $this->SessionQueue[0]);

		$this->SessionQueue[0] = 'fuga';
		$this->SessionQueue[] = 'hoge';
		$this->assertSame('fuga', $this->SessionQueue[0]);
		$this->assertSame('hoge', $this->SessionQueue[1]);
		$this->assertSame(2, count($this->SessionQueue));

		$iterated = array();
		foreach ($this->SessionQueue as $key => $val) {
			$iterated[$key] = $val;
		}
		$this->assertSame(array('fuga', 'hoge'), $iterated);

		$this->assertTrue(isset($this->SessionQueue[0]));
		unset($this->SessionQueue[0]);
		$this->assertFalse(isset($this->SessionQueue[0]));
	}

	public function testConveniences() {
		$this->assertTrue($this->SessionQueue->isEmpty());

		$this->SessionQueue->push('hoge');
		$this->assertFalse($this->SessionQueue->isEmpty());

		$this->assertSame('hoge', $this->SessionQueue->pop());
		$this->assertTrue($this->SessionQueue->isEmpty());

		$this->SessionQueue->push('piyo');
		$this->SessionQueue->unshift('fuga');
		$this->assertFalse($this->SessionQueue->isEmpty());
		$this->assertSame('fuga', $this->SessionQueue->shift());
	}

	public function testSearch() {

		$this->SessionQueue->write(array(
			array('name' => 'hoge'),
			array('name' => 'fuga'),
			array('name' => 'fuga'),
		));
		$this->assertFalse($this->SessionQueue->search(array('name' => 'piyo')));
		$this->assertSame(1, $this->SessionQueue->search(array('name' => 'fuga')));
		$this->assertSame(array(1, 2), $this->SessionQueue->search(array('name' => 'fuga'), array('multiple' => true)));

		$this->SessionQueue->write(array(
			1,
			2,
			2,
		));
		$this->assertFalse($this->SessionQueue->search('1', array('strict' => true)));
		$this->assertSame(1, $this->SessionQueue->search(2, array('strict' => true)));
		$this->assertSame(array(1, 2), $this->SessionQueue->search(2, array('strict' => true, 'multiple' => true)));
	}

}
