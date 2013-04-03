<?php

App::import('TestSuite', 'Ninja.NinjaBehaviorTestCase');


class MagickMethodBehaviorMockBase extends Model {

	public $useTable = false;

	public $hasField = true;

	public function find($type = 'first', $query = array()) {
		$args = func_get_args();
		return $args;
	}

	/*
	 * @override
	 */
	public function hasField($name, $checkVirtual = false) {
		return $this->hasField;
	}

	public function field($name, $conditions = NULL, $order = NULL) {
		$args = func_get_args();
		return $args;
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

class MagickMethodBehaviorTest extends NinjaBehaviorTestCase {

	public function testNormalConditions() {

		$result = $this->Model->findById(1);
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('id') => 1,
		)));
		$this->assertEquals($result, $expected);

		$result = $this->Model->findAllById(1);
		$expected = array('all', array('conditions' => array(
			$this->Model->escapeField('id') => 1,
		)));
		$this->assertEquals($result, $expected);

		$result = $this->Model->findAllById(array(1, 2));
		$expected = array('all', array('conditions' => array(
			$this->Model->escapeField('id') => array(1, 2),
		)));
		$this->assertEquals($result, $expected);

		$result = $this->Model->findByUserId(1);
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('user_id') => 1,
		)));
		$this->assertEquals($result, $expected);

		$result = $this->Model->findByIdAndUserNameAndPassword(1, 'john', 'I love you');
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('id') => 1,
			$this->Model->escapeField('user_name') => 'john',
			$this->Model->escapeField('password') => 'I love you',
		)));
		$this->assertEquals($result, $expected);

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
		$this->assertEquals($result, $expected);

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
		$this->assertEquals($result, $expected);

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
		$this->assertEquals($result, $expected);

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
		$this->assertEquals($result, $expected);

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
		$this->assertEquals($result, $expected);

	}

	public function testCallbacks() {

		$result = $this->Model->findByInsertId();
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('id') => 2,
		)));
		$this->assertEquals($result, $expected);

		$result = $this->Model->findByUserIdAndInsertId(1);
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('user_id') => 1,
			$this->Model->escapeField('id') => 2,
		)));
		$this->assertEquals($result, $expected);

		$result = $this->Model->findByUserDefined();
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('specific_field') => 'value!',
		)));
		$this->assertEquals($result, $expected);

		$result = $this->Model->findByMultiParameters();
		$expected = array('first', array('conditions' => array(
			'hoge' => 'piyo',
			'fuga' => 'moge',
			'untara' => 1,
		)));
		$this->assertEquals($result, $expected);

	}

	public function testNoScope() {

		$result = $this->Model->findCount();
		$expected = array('count', array());
		$this->assertEquals($result, $expected);

		$result = $this->Model->findUserFindType();
		$expected = array('userFindType', array());
		$this->assertEquals($result, $expected);

		$result = $this->Model->findCount(array('contain' => false));
		$expected = array('count', array('contain' => false));
		$this->assertEquals($result, $expected);

	}

	public function testAssociatedField() {

		$this->Model->hasField = false;

		$result = $this->Model->findByBelongsToField('belongs to');
		$expected = array('first', array('conditions' => array(
			$this->Model->BelongsTo->escapeField('field') => 'belongs to',
		)));
		$this->assertEquals($result, $expected);

		$result = $this->Model->findByHasOneField('has one');
		$expected = array('first', array('conditions' => array(
			$this->Model->HasOne->escapeField('field') => 'has one',
		)));
		$this->assertEquals($result, $expected);

		$result = $this->Model->findByHasManyField('has many');
		$expected = array('first', array('conditions' => array(
			$this->Model->HasMany->escapeField('field') => 'has many',
		)));
		$this->assertEquals($result, $expected);

		$result = $this->Model->findByHabtmField('has and belongs to many');
		$expected = array('first', array('conditions' => array(
			$this->Model->Habtm->escapeField('field') => 'has and belongs to many',
		)));
		$this->assertEquals($result, $expected);

		$this->Model->BelongsTo->hasField = false;

		$result = $this->Model->findByBelongsToField('belongs to');
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('belongs_to_field') => 'belongs to',
		)));
		$this->assertEquals($result, $expected);

	}

	public function testScope() {

		$result = $this->Model->scopeId(1);
		$expected = array('conditions' => array(
			$this->Model->escapeField('id') => 1,
		));
		$this->assertEquals($result, $expected);

		$result = $this->Model->scopeInsertId();
		$expected = array('conditions' => array(
			$this->Model->escapeField('id') => 2,
		));
		$this->assertEquals($result, $expected);

		$result = $this->Model->scopeEnabledAndInsertId(true);
		$expected = array('conditions' => array(
			$this->Model->escapeField('enabled') => true,
			$this->Model->escapeField('id') => 2,
		));
		$this->assertEquals($result, $expected);

	}

	public function testField() {

		$result = $this->Model->fieldById('hoge', 1);
		$expected = array('hoge', array(
			$this->Model->escapeField('id') => 1,
		));
		$this->assertEquals($result, $expected);

		$result = $this->Model->fieldByInsertId('fuga');
		$expected = array('fuga', array(
			$this->Model->escapeField('id') => 2,
		));
		$this->assertEquals($result, $expected);

		$result = $this->Model->fieldByEnabledAndInsertId('piyo', true);
		$expected = array('piyo', array(
			$this->Model->escapeField('enabled') => true,
			$this->Model->escapeField('id') => 2,
		));
		$this->assertEquals($result, $expected);

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