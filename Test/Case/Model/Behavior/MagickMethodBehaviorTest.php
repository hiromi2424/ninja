<?php

App::import('TestSuite', 'Ninja.NinjaBehaviorTestCase');


class MagickMethodBehaviorMockBase extends Model {

	public $useTable = false;

	public $hasField = true;

	public function find() {
		$args = func_get_args();
		return $args;
	}

	/*
	 * @override
	 */
	public function hasField() {
		return $this->hasField;
	}

}


class MagickMethodBehaviorMockModel extends MagickMethodBehaviorMockBase {

	public $actsAs = array('Ninja.MagickMethod');

	public $belongsTo = array(
		'BelongsTo' => array(
			'className' => 'MagickMethodBehaviorMockBelongsTo',
		),
	);
	public $hasOne = array(
		'HasOne' => array(
			'className' => 'MagickMethodBehaviorMockHasOne',
		),
	);
	public $hasMany = array(
		'HasMany' => array(
			'className' => 'MagickMethodBehaviorMockHasMany',
		),
	);
	public $hasAndBelongsToMany = array(
		'Habtm' => array(
			'className' => 'MagickMethodBehaviorMockHabtm',
			'with' => 'MagickMethodBehaviorMockHabtmsMagickMethodBehaviorMockModel',
		),
	);

	public function getInsertId() {
		return 2;
	}

	public function byUserDefined() {
		return array($this->escapeField('specific_field') => 'value!');
	}

	public function byMultiParameters() {
		return array(
			'hoge' => 'piyo',
			'fuga' => 'moge',
			'untara' => 1,
		);
	}

}

class MagickMethodBehaviorMockHasOne extends MagickMethodBehaviorMockBase {

}

class MagickMethodBehaviorMockBelongsTo extends MagickMethodBehaviorMockBase {

}

class MagickMethodBehaviorMockHasMany extends MagickMethodBehaviorMockBase {

}

class MagickMethodBehaviorMockHabtmsMagickMethodBehaviorMockModel extends MagickMethodBehaviorMockBase {

}

class MagickMethodBehaviorMockHabtm extends MagickMethodBehaviorMockBase {

}

class MagickMethodBehaviorTestCase extends NinjaBehaviorTestCase {

	public function testNormalConditions() {

		$result = $this->Model->findById(1);
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('id') => 1,
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findAllById(1);
		$expected = array('all', array('conditions' => array(
			$this->Model->escapeField('id') => 1,
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findAllById(array(1, 2));
		$expected = array('all', array('conditions' => array(
			$this->Model->escapeField('id') => array(1, 2),
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByUserId(1);
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('user_id') => 1,
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByIdAndUserNameAndPassword(1, 'john', 'I love you');
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('id') => 1,
			$this->Model->escapeField('user_name') => 'john',
			$this->Model->escapeField('password') => 'I love you',
		)));
		$this->assertEqual($expected, $result);

	}

	public function testOrConditions() {

		$result = $this->Model->findByIdOrUserId(1, 2);
		$expected = array('first', array('conditions' => array(
			array(
				'OR' => array(
					$this->Model->escapeField('id') => 1,
					$this->Model->escapeField('user_id') => 2,
				),
			)
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByIdOrUserIdOrName(1, 2, 'john');
		$expected = array('first', array('conditions' => array(
			array(
				'OR' => array(
					$this->Model->escapeField('id') => 1,
					$this->Model->escapeField('user_id') => 2,
				$this->Model->escapeField('name') => 'john',
				),
			)
		)));
		$this->assertEqual($expected, $result);

	}

	public function testComplicatedConditions() {

		$result = $this->Model->findByIdOrUserIdAndName(1, 2, 'john');
		$expected = array('first', array('conditions' => array(
			array(
				'OR' => array(
					$this->Model->escapeField('id') => 1,
					$this->Model->escapeField('user_id') => 2,
				),
			),
			$this->Model->escapeField('name') => 'john',
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByNameAndIdOrUserId('john', 1, 2);
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('name') => 'john',
			array(
				'OR' => array(
					$this->Model->escapeField('id') => 1,
					$this->Model->escapeField('user_id') => 2,
				),
			),
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByAgeOrNameAndIdOrUserId(12, 'john', 1, 2);
		$expected = array('first', array('conditions' => array(
			array(
				'OR' => array(
					$this->Model->escapeField('age') => 12,
					$this->Model->escapeField('name') => 'john',
				),
			),
			array(
				'OR' => array(
					$this->Model->escapeField('id') => 1,
					$this->Model->escapeField('user_id') => 2,
				),
			)
		)));
		$this->assertEqual($expected, $result);

	}

	public function testCallbacks() {

		$result = $this->Model->findByInsertId();
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('id') => 2,
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByUserIdAndInsertId(1);
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('user_id') => 1,
			$this->Model->escapeField('id') => 2,
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByUserDefined();
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('specific_field') => 'value!',
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByMultiParameters();
		$expected = array('first', array('conditions' => array(
			'hoge' => 'piyo',
			'fuga' => 'moge',
			'untara' => 1,
		)));
		$this->assertEqual($expected, $result);

	}

	public function testNoScope() {

		$result = $this->Model->findCount();
		$expected = array('count', array());
		$this->assertEqual($expected, $result);

		$result = $this->Model->findUserFindType();
		$expected = array('userFindType', array());
		$this->assertEqual($expected, $result);

		$result = $this->Model->findCount(array('contain' => false));
		$expected = array('count', array('contain' => false));
		$this->assertEqual($expected, $result);

	}

	public function testAssociatedField() {

		$this->Model->hasField = false;

		$result = $this->Model->findByBelongsToField('belongs to');
		$expected = array('first', array('conditions' => array(
			$this->Model->BelongsTo->escapeField('field') => 'belongs to',
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByHasOneField('has one');
		$expected = array('first', array('conditions' => array(
			$this->Model->HasOne->escapeField('field') => 'has one',
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByHasManyField('has many');
		$expected = array('first', array('conditions' => array(
			$this->Model->HasMany->escapeField('field') => 'has many',
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByHabtmField('has and belongs to many');
		$expected = array('first', array('conditions' => array(
			$this->Model->Habtm->escapeField('field') => 'has and belongs to many',
		)));
		$this->assertEqual($expected, $result);

		$this->Model->BelongsTo->hasField = false;

		$result = $this->Model->findByBelongsToField('belongs to');
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('belongs_to_field') => 'belongs to',
		)));
		$this->assertEqual($expected, $result);

	}

	public function testScope() {

		$result = $this->Model->scopeId(1);
		$expected = array('conditions' => array(
			$this->Model->escapeField('id') => 1,
		));
		$this->assertEqual($expected, $result);

		$result = $this->Model->scopeInsertId();
		$expected = array('conditions' => array(
			$this->Model->escapeField('id') => 2,
		));
		$this->assertEqual($expected, $result);

		$result = $this->Model->scopeEnabledAndInsertId(true);
		$expected = array('conditions' => array(
			$this->Model->escapeField('enabled') => true,
			$this->Model->escapeField('id') => 2,
		));
		$this->assertEqual($expected, $result);

	}

	public function testHasMethod() {
		$this->assertTrue($this->Model->hasMethod('find'));
		$this->assertTrue($this->Model->hasMethod('byInsertId'));
		$this->assertTrue($this->Model->hasMethod('findById'));
		$this->assertFalse($this->Model->hasMethod('undefined'));
	}

	public function testErrors() {

		try {
			$this->Model->findById();
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (Exception $e) {
			$this->assertInstanceOf('BadMethodCallException', $e);
		}

		try {
			$this->Model->findByIdAndId(1, 2);
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (Exception $e) {
			$this->assertInstanceOf('BadMethodCallException', $e);
		}

		try {
			$this->Model->findByIdOr(1);
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (Exception $e) {
			$this->assertInstanceOf('BadMethodCallException', $e);
		}

	}

}