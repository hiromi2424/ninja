<?php

App::import('TestSuite', 'Ninja.NinjaBehaviorTestCase');

class ConfigListBehaviorMockModel extends Model {
	public $actsAs = array('Ninja.ConfigList');
}

class ConfigListBehaviorTest extends NinjaBehaviorTestCase {

	public $fixtures = array('plugin.Ninja.ConfigListBehaviorMockModel');

	protected $_config = array(
		'lists' => array(
			1 => 'hoge',
			'piyo' => 'fuga',
			2 => 'hoge{{',
		),
	);

	public function setUp() {
		parent::setUp();
		Configure::write('TestConfig.ConfigListBehaviorMockModel', $this->_config);
		$this->_reattach(array('config' => 'TestConfig.:class'));
	}

	public function tearDown() {
		if (Configure::read('TestConfig')) {
			Configure::delete('TestConfig');
		}
		parent::tearDown();
	}

	protected function _reattach($options = array()) {
		parent::_reattach($options + array('config' => 'TestConfig.:class'));
	}

	public function testFind() {

		$expected = array(
			array(
				$this->Model->alias => array(
					'id' => 1,
					'normal' => 'normal string',
					'list' => array(1, 'piyo'),
				),
			)
		);
		$this->assertEqual($this->Model->find('all'), $expected);

		$expected = array(
			$this->Model->alias => array(
				'id' => 1,
				'normal' => 'normal string',
				'list' => array(1, 'piyo'),
			),
		);
		$this->assertEqual($this->Model->find('first'), $expected);

		$this->_reattach(array('separator' => ','));
		$expected = array(
			$this->Model->alias => array(
				'id' => 1,
				'normal' => 'normal string',
				'list' => array(2, '}}fuga'),
			),
		);
		$this->assertEqual($this->Model->find('first'), $expected);

		$this->_reattach(array('separator' => 'not match'));
		$expected = array(
			$this->Model->alias => array(
				'id' => 1,
				'normal' => 'normal string',
				'list' => 'hoge{{,}}fuga',
			),
		);
		$this->assertEqual($this->Model->find('first'), $expected);

		$this->_reattach(array('fields' => 'other'));
		$expected = array(
			$this->Model->alias => array(
				'id' => 1,
				'normal' => 'normal string',
				'list' => 'hoge{{,}}fuga',
			),
		);
		$this->assertEqual($this->Model->find('first'), $expected);

		Configure::write('TestConfig.ConfigListBehaviorMockModel.List', $this->_config);
		$this->_reattach(array('config' => 'TestConfig.:class.List'));
		$expected = array(
			$this->Model->alias => array(
				'id' => 1,
				'normal' => 'normal string',
				'list' => array(1, 'piyo'),
			),
		);
		$this->assertEqual($this->Model->find('first'), $expected);

		$this->Model->updateAll(array('list' => null));
		$expected = array(
			$this->Model->alias => array(
				'id' => 1,
				'normal' => 'normal string',
				'list' => null,
			),
		);
		$this->assertEqual($this->Model->find('first'), $expected);
	}

	public function testSave() {
		$result = $this->Model->save(array('id' => 1, 'normal' => 'normal', 'list' => array(1, 2)));
		$expected = array($this->Model->alias => array('id' => 1, 'normal' => 'normal', 'list' => 'hoge{{,}}hoge{{'));
		$this->assertIdentical($result, $expected);

		$this->_reattach(array('fields' => 'other'));
		$this->expectError();
		ob_start();
		$this->assertFalse($this->Model->save(array('id' => 1, 'normal' => 'normal', 'list' => array(1, 2))));
		ob_end_clean();

		$this->_reattach();
		$result = $this->Model->save(array('id' => 1, 'normal' => 'normal', 'list' => 'aaa'));
		$expected = array($this->Model->alias => array('id' => 1, 'normal' => 'normal', 'list' => 'aaa'));
	}

	public function testInConfigList() {
		$this->Model->validate = array(
			'list' => array(
				'rule' => array('inConfigList'),
			),
		);

		$this->Model->set(array(
			'list' => array(1 , 2),
		));
		$this->assertTrue($this->Model->validates());

		$this->Model->set(array(
			'list' => 2,
		));
		$this->assertTrue($this->Model->validates());

		$this->Model->set(array(
			'list' => array(1 , 100),
		));
		$this->assertFalse($this->Model->validates());

		$this->Model->set(array(
			'list' => 100,
		));
		$this->assertFalse($this->Model->validates());
	}

}