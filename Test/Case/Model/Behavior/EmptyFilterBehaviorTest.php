<?php
/* CommonValidation Test cases generated on: 2010-11-23 09:11:17 : 1290472577*/
App::import('TestSuite', 'Ninja.NinjaBehaviorTestCase');

class EmptyFilterBehaviorMockModel extends Model {
	public $actsAs = array('Ninja.EmptyFilter');
}

class EmptyFilterBehaviorTest extends NinjaBehaviorTestCase {

	public $fixtures = array('plugin.Ninja.EmptyFilterBehaviorMockModel');

	public function testTrimSpaces() {
		$this->assertSame('a', $this->Model->trimSpaces(' a '));
		$this->assertSame('', $this->Model->trimSpaces('      '));
		$this->assertSame(array(' a ', ' b '), $this->Model->trimSpaces(array(' a ', ' b ')));
		$this->assertSame(array('string' => 'a', 'unknown' => ' b '), $this->Model->trimSpaces(array('string' => ' a ', 'unknown' => ' b ')));
		$this->assertSame(array('EmptyFilterBehaviorMockModel' => array('string' => 'a', 'unknown' => ' b ')), $this->Model->trimSpaces(array('EmptyFilterBehaviorMockModel' => array('string' => ' a ', 'unknown' => ' b '))));
	}

	public function testFilterEmpty() {
		$this->assertSame(null, $this->Model->filterEmpty(null));
		$this->assertSame(false, $this->Model->filterEmpty(false));
		$this->assertSame(1, $this->Model->filterEmpty(1));
		$this->assertSame("", $this->Model->filterEmpty(""));
		$this->assertSame("hoge", $this->Model->filterEmpty("hoge"));
		$this->assertSame(array(), $this->Model->filterEmpty(array()));
		$this->assertSame(array(), $this->Model->filterEmpty(array('Empty' => '')));
		$this->assertSame(array(), $this->Model->filterEmpty(array('EmptyFilterBehaviorMockModel' => array())));

		$result = $this->Model->filterEmpty(array(
			'EmptyFilterBehaviorMockModel' => array(
				'string' => ''
			),
		));
		$expected = array();
		$this->assertSame($expected, $result);

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
		$this->assertSame($expected, $result);

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
		$this->assertSame($expected, $result);

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
		$this->assertSame($expected, $result);

		$result = $this->Model->filterEmpty(array(
			'EmptyFilterBehaviorMockModel' => array(
				'string' => '',
				'binary' => '',
			),
		));
		$expected = array();
		$this->assertSame($expected, $result);

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
		$this->assertSame($expected, $result);
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
		$result = $this->Model->validates();
		$this->assertTrue($result);
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
