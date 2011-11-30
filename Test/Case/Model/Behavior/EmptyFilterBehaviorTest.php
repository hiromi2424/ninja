<?php
/* CommonValidation Test cases generated on: 2010-11-23 09:11:17 : 1290472577*/
App::import('Lib', 'Ninja.test' . DS . 'NinjaBehaviorTestCase');

class EmptyFilterBehaviorMockModel extends Model {
	public $actsAs = array('Ninja.EmptyFilter');
}

class EmptyFilterBehaviorTestCase extends NinjaBehaviorTestCase {

	public $fixtures = array('plugin.ninja.empty_filter_behavior_mock_model');

	public function testTrimSpaces() {
		$this->assertIdentical($this->Model->trimSpaces(' a '), 'a');
		$this->assertIdentical($this->Model->trimSpaces('      '), '');
		$this->assertIdentical($this->Model->trimSpaces(array(' a ', ' b ')), array(' a ', ' b '));
		$this->assertIdentical($this->Model->trimSpaces(array('string' => ' a ', 'unknown' => ' b ')), array('string' => 'a', 'unknown' => ' b '));
		$this->assertIdentical($this->Model->trimSpaces(array('EmptyFilterBehaviorMockModel' => array('string' => ' a ', 'unknown' => ' b '))), array('EmptyFilterBehaviorMockModel' => array('string' => 'a', 'unknown' => ' b ')));
	}

	public function testFilterEmpty() {
		$this->assertIdentical($this->Model->filterEmpty(null), null);
		$this->assertIdentical($this->Model->filterEmpty(false), false);
		$this->assertIdentical($this->Model->filterEmpty(1), 1);
		$this->assertIdentical($this->Model->filterEmpty(""), "");
		$this->assertIdentical($this->Model->filterEmpty("hoge"), "hoge");
		$this->assertIdentical($this->Model->filterEmpty(array()), array());
		$this->assertIdentical($this->Model->filterEmpty(array('Empty' => '')), array());
		$this->assertIdentical($this->Model->filterEmpty(array('EmptyFilterBehaviorMockModel' => array())), array());

		$result = $this->Model->filterEmpty(array(
			'EmptyFilterBehaviorMockModel' => array(
				'string' => ''
			),
		));
		$expected = array();
		$this->assertIdentical($result, $expected);

		$result = $this->Model->filterEmpty(array(
			'EmptyFilterBehaviorMockModel' => array(
				'string' => 1,
			),
		));
		$expected = array(
			'EmptyFilterBehaviorMockModel' => array(
				'string' => 1,
			),
		);
		$this->assertIdentical($result, $expected);

		$result = $this->Model->filterEmpty(array(
			'EmptyFilterBehaviorMockModel' => array(
				'string' => 'hoge',
				'binary' => 'piyo',
			),
		));
		$expected = array(
			'EmptyFilterBehaviorMockModel' => array(
				'string' => 'hoge',
				'binary' => 'piyo',
			),
		);
		$this->assertIdentical($result, $expected);

		$result = $this->Model->filterEmpty(array(
			'EmptyFilterBehaviorMockModel' => array(
				'string' => 0,
				'binary' => '',
			),
		));
		$expected = array(
			'EmptyFilterBehaviorMockModel' => array(
				'string' => 0,
			),
		);
		$this->assertIdentical($result, $expected);

		$result = $this->Model->filterEmpty(array(
			'EmptyFilterBehaviorMockModel' => array(
				'string' => '',
				'binary' => '',
			),
		));
		$expected = array();
		$this->assertIdentical($result, $expected);

		$result = $this->Model->filterEmpty(array(
			array(
				'EmptyFilterBehaviorMockModel' => array(
					'string' => 0,
					'binary' => '',
				),
				'RelationalModel' => array(
					'hoge' => 0,
					'piyo' => false,
				),
			),
		));
		$expected = array(
			array(
				'EmptyFilterBehaviorMockModel' => array(
					'string' => 0,
				),
				'RelationalModel' => array(
					'hoge' => 0,
				),
			)
		);
		$this->assertIdentical($result, $expected);
	}

	public function testBeforeValidate() {

		$this->Model->set(array(
			'EmptyFilterBehaviorMockModel' => array(
				'string' => '    ',
				'integer' => ' a  ',
				'binary' => '   b   ',
			),
			'RelationalModel' => array(
				'hoge' => ' c ',
				'piyo' => '  d  ',
			),
		));
		$this->assertTrue($this->Model->validates());
		$this->assertIdentical($this->Model->data, array(
			'EmptyFilterBehaviorMockModel' => array(
				'integer' => 'a',
				'binary' => '   b   ',
			),
			'RelationalModel' => array(
				'hoge' => ' c ',
				'piyo' => '  d  ',
			),
		));
	}

	function testIllegal() {
		$this->expectError();
		$this->Model->trimSpaces(new stdClass);
	}
}
