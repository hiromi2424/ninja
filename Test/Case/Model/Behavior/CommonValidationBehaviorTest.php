<?php
/* CommonValidation Test cases generated on: 2010-11-23 09:11:17 : 1290472577*/
App::import('TestSuite', 'Ninja.NinjaBehaviorTestCase');

class CommonValidationBehaviorMockModel extends Model {
	public $actsAs = array('Ninja.CommonValidation' => array('userModel' => 'CommonValidationBehaviorUser'));
	public $validate = array(
		'common_validation_behavior_belonged_id' => array(
			'existsForeign' => array(
				'rule' => array('existsForeign'),
			),
		),
	);

	public $belongsTo = array(
		'SpecialBelonged' => array(
			'className' => 'CommonValidationBehaviorBelonged',
			'foreignKey' => 'common_validation_behavior_belonged_id',
		),
		'CommonValidationBehaviorUser',
	);
	public $hasMany = array(
		'CommonValidationBehaviorBelonged',
	);
}

class CommonValidationBehaviorUser extends Model {
	public $hasOne = array(
		'CommonValidationBehaviorMockModel',
	);
}

class CommonValidationBehaviorBelonged extends Model {
	public $belongsTo = array(
		'CommonValidationBehaviorMockModel',
		'CommonValidationBehaviorUser',
	);
}

class CommonValidationBehaviorTest extends NinjaBehaviorTestCase {
	public $fixtures = array(
		'plugin.Ninja.CommonValidationBehaviorMockModel',
		'plugin.Ninja.CommonValidationBehaviorBelonged',
		'plugin.Ninja.CommonValidationBehaviorUser',
	);

	public function testIsCurrentUser() {
		Configure::write('CurrentUser.id', 1);
		$this->assertFalse($this->Model->isCurrentUser(array('common_validation_behavior_user_id' => 3)));
		$this->assertTrue($this->Model->isCurrentUser(array('common_validation_behavior_user_id' => 1)));

		try {
			$this->Model->isCurrentUser(array('common_validation_behavior_user_id' => null));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/common_validation_behavior_user_id/', $e->getMessage());
		}

		try {
			Configure::write('CurrentUser.id', null);
			$this->Model->isCurrentUser(array('common_validation_behavior_user_id' => 1));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/CurrentUser\.id/', $e->getMessage());
		}
	}

	public function testIsNotCurrentUser() {
		Configure::write('CurrentUser.id', 1);
		$this->assertTrue($this->Model->isNotCurrentUser(array('common_validation_behavior_user_id' => 3)));
		$this->assertFalse($this->Model->isNotCurrentUser(array('common_validation_behavior_user_id' => 1)));
	}

	public function testCurrentUserHas() {
		Configure::write('CurrentUser.id', 2);
		$this->assertTrue($this->Model->currentUserHas(array('common_validation_behavior_belonged_id' => 1)));

		Configure::write('CurrentUser.id', 1);
		$this->assertFalse($this->Model->currentUserHas(array('common_validation_behavior_belonged_id' => 1)));

		try {
			Configure::write('CurrentUser.id', null);
			$this->Model->currentUserHas(array('common_validation_behavior_belonged_id' => 1));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/CurrentUser\.id/', $e->getMessage());
		}

		try {
			Configure::write('CurrentUser.id', 2);
			$this->Model->currentUserHas(array('not_valid_id' => 1));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/not_valid_id/', $e->getMessage());
		}

		try {
			ClassRegistry::init('CommonValidationBehaviorBelonged')->belongsTo = array();
			Configure::write('CurrentUser.id', 2);
			$this->Model->currentUserHas(array('common_validation_behavior_belonged_id' => 1));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/CommonValidationBehaviorBelonged/', $e->getMessage());
		}
	}

	public function testCurrentUserHasNot() {
		Configure::write('CurrentUser.id', 2);
		$this->assertFalse($this->Model->currentUserHasNot(array('common_validation_behavior_belonged_id' => 1)));

		Configure::write('CurrentUser.id', 1);
		$this->assertTrue($this->Model->currentUserHasNot(array('common_validation_behavior_belonged_id' => 1)));
	}

	public function testHasThis() {
		$this->Model->id = array(1, 2);
		$this->assertFalse($this->Model->hasThis(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = 1;
		$this->assertFalse($this->Model->hasThis(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = 2;
		$this->assertFalse($this->Model->hasThis(array('common_validation_behavior_belonged_id' => 2)));
		$this->assertTrue($this->Model->hasThis(array('common_validation_behavior_belonged_id' => 1)));

		try {
			$this->Model->hasThis(array('common_validation_behavior_belonged_id' => null));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/common_validation_behavior_belonged_id/', $e->getMessage());
		}

		try {
			$this->Model->hasThis(array('not_valid_id' => 1));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/not_valid_id/', $e->getMessage());
		}

		try {
			$this->Model->id = false;
			$this->Model->hasThis(array('common_validation_behavior_belonged_id' => 1));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/CommonValidationBehaviorMockModel/', $e->getMessage());
		}
	}

	public function testHasNotThis() {
		try {
			$this->Model->id = false;
			$this->Model->hasNotThis(array('common_validation_behavior_belonged_id' => 1));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/CommonValidationBehaviorMockModel/', $e->getMessage());
		}

		$this->Model->id = array(2, 1);
		$this->assertTrue($this->Model->hasNotThis(array('common_validation_behavior_belonged_id' => 2)));
		$this->assertFalse($this->Model->hasNotThis(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = 1;
		$this->assertTrue($this->Model->hasNotThis(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = 2;
		$this->assertTrue($this->Model->hasNotThis(array('common_validation_behavior_belonged_id' => 2)));
		$this->assertFalse($this->Model->hasNotThis(array('common_validation_behavior_belonged_id' => 1)));
	}

	public function testThisHas() {
		try {
			$this->Model->id = false;
			$this->Model->thisHas(array('common_validation_behavior_belonged_id' => 1));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/CommonValidationBehaviorMockModel/', $e->getMessage());
		}

		$this->Model->id = array(1, 2);
		$this->assertTrue($this->Model->thisHas(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = 3;
		$this->assertFalse($this->Model->thisHas(array('common_validation_behavior_belonged_id' => 5)));

		$this->Model->id = 4;
		$this->assertTrue($this->Model->thisHas(array('common_validation_behavior_belonged_id' => 5)));

		try {
			$this->Model->thisHas(array('common_validation_behavior_belonged_id' => null));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/thisHas/', $e->getMessage());
		}

		try {
			$this->Model->thisHas(array('not_valid_id' => 1));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/not_valid_id/', $e->getMessage());
		}
	}

	public function testThisHasNot() {
		try {
			$this->Model->id = false;
			$this->Model->thisHasNot(array('common_validation_behavior_belonged_id' => 1));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/CommonValidationBehaviorMockModel/', $e->getMessage());
		}

		$this->Model->id = array(1, 2);
		$this->assertFalse($this->Model->thisHasNot(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = 3;
		$this->assertTrue($this->Model->thisHasNot(array('common_validation_behavior_belonged_id' => 5)));

		$this->Model->id = 4;
		$this->assertFalse($this->Model->thisHasNot(array('common_validation_behavior_belonged_id' => 5)));

		try {
			$this->Model->thisHasNot(array('common_validation_behavior_belonged_id' => null));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/common_validation_behavior_belonged_id/', $e->getMessage());
		}

		try {
			$this->Model->thisHasNot(array('not_valid_id' => 1));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/not_valid_id/', $e->getMessage());
		}
	}

	public function testNotExists() {
		$this->Model->set(array('id' => 1));
		$this->assertFalse($this->Model->notExists());

		$this->Model->set(array('id' => 100));
		$this->assertTrue($this->Model->notExists());
	}

	public function testExistsForeign() {
		$this->assertFalse($this->Model->existsForeign(array('common_validation_behavior_belonged_id' => 100)));
		$this->assertTrue($this->Model->existsForeign(array('common_validation_behavior_belonged_id' => 5)));

		try {
			$this->Model->existsForeign(array('common_validation_behavior_belonged_id' => null));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/common_validation_behavior_belonged_id/', $e->getMessage());
		}

		try {
			$this->Model->existsForeign(array('not_valid_id' => 1));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/not_valid_id/', $e->getMessage());
		}
	}

	public function testEmptyValue() {
		$this->assertTrue($this->Model->existsForeign(array('common_validation_behavior_belonged_id' => '')));
	}

	public function testNotExistsForeign() {
		$this->assertFalse($this->Model->notExistsForeign(array('common_validation_behavior_belonged_id' => 5)));
		$this->assertTrue($this->Model->notExistsForeign(array('common_validation_behavior_belonged_id' => 100)));

		try {
			$this->Model->notExistsForeign(array('common_validation_behavior_belonged_id' => null));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/common_validation_behavior_belonged_id/', $e->getMessage());
		}

		try {
			$this->Model->notExistsForeign(array('not_valid_id' => 1));
			$this->fail('Expected BadMethodCallException was not thrown');
		} catch (BadMethodCallException $e) {
			$this->assertRegExp('/not_valid_id/', $e->getMessage());
		}
	}

	public function testValidation() {
		$data = array(
			$this->Model->alias => array(
				'common_validation_behavior_belonged_id' => 1,
			),
		);
		$this->Model->set($data);
		$this->assertTrue($this->Model->validates());

		//$this->Model->validate['common_validation_behavior_belonged_id']['existsForeign']['rule'][] = 'Not Valid Foreign Model';
		//$this->assertFalse($this->Model->validates());

		$this->Model->validate['common_validation_behavior_belonged_id']['existsForeign']['rule'][] = 'CommonValidationBehaviorBelonged';
		$this->assertTrue($this->Model->validates());

	}

	public function testMaxCount() {
		$this->Model->validate = array(
			'common_validation_behavior_belonged_id' => array(
				'maxCount' => array(
					'rule' => array('maxCount'),
				),
			),
		);
		$data = array(
			'common_validation_behavior_belonged_id' => 5,
			'common_validation_behavior_user_id' => null,
		);

		$this->assertFalse($this->Model->save($data));

		$this->Model->validate['common_validation_behavior_belonged_id']['maxCount']['rule'][] = 3;
		$this->assertTrue(!!$this->Model->save($data));
		$this->assertFalse($this->Model->save($data));
	}

	public function testCheckDoubleSave() {
		// workaround: formatter was replaced with CakeTestDispatcher::date(),
		// that provides test start time so this test case failed.
		$db = $this->Model->getDataSource();
		$formatterBackup = $db->columns['datetime']['formatter'];
		$db->columns['datetime']['formatter'] = 'date';

		$this->Model->Behaviors->attach('Ninja.CommonValidation', array(
			'waitDoubleCheck' => 10,
		));

		$this->Model->validate = array(
			'created' => array(
				'checkDoubleSave' => array(
					'rule' => array('checkDoubleSave', 'common_validation_behavior_belonged_id', 'common_validation_behavior_user_id'),
				),
			),
		);
		$data = array(
			'common_validation_behavior_belonged_id' => 4,
			'common_validation_behavior_user_id' => 4,
		);
		$result = $this->Model->save($this->Model->create($data));
		$this->assertTrue(!!$result);
		$result = $this->Model->save($this->Model->create($data));
		$this->assertFalse($result);
		$this->assertTrue(Set::check($this->Model->validationErrors, 'created'));

		$this->Model->validate = array(
			'created' => array(
				'checkDoubleSave' => array(
					'rule' => array('checkDoubleSave'),
				),
			),
		);
		$this->assertFalse($this->Model->save($this->Model->create($data)));
		$this->assertTrue(Set::check($this->Model->validationErrors, 'created'));

		$this->Model->validate['created']['checkDoubleSave']['rule'][] = 5;
		$this->assertFalse($this->Model->save($this->Model->create($data)));
		$this->assertTrue(Set::check($this->Model->validationErrors, 'created'));


		$db->columns['datetime']['formatter'] = $formatterBackup;
	}

	public function testGenerateFormatedDatetime() {
		$result = $this->Model->generateFormatedDatetime(strtotime('2010-09-26 14:57:38'));
		$expected = array('year' => '2010', 'month' => '09', 'day' => '26', 'hour' => '14', 'min' => '57', 'sec' => '38');
		$this->assertEquals($expected, $result);

		$result = $this->Model->deconstruct('created', $result);
		$expected = '2010-09-26 14:57:38';
		$this->assertEquals($expected, $result);
	}

	public function testDatetime() {
		$this->assertFalse($this->Model->datetime(array('datetime' => '2010/10')));
		if (false !== strpos(PHP_OS, 'WIN')) {
			$this->assertFalse($this->Model->datetime(array('datetime' => '9999/11/11 11:11:11')));
			$this->assertFalse($this->Model->datetime(array('datetime' => '0001/11/11 11:11:11')));
		}
		$this->assertFalse($this->Model->datetime(array('datetime' => '2010/14/11')));
		$this->assertFalse($this->Model->datetime(array('datetime' => time())));
		$this->assertFalse($this->Model->datetime(array('datetime' => true)));

		$this->assertTrue($this->Model->datetime(array('datetime' => '2010/10/11')));
		$this->assertTrue($this->Model->datetime(array('datetime' => '2010/11/11 11:11:11')));
		$this->assertTrue($this->Model->datetime(array('datetime' => '2010/11/11 11:11')));
		$this->assertTrue($this->Model->datetime(array('datetime' => '11:11')));
		$this->assertTrue($this->Model->datetime(array('datetime' => '11:11:10')));
	}

	public function testConvertDatetime() {
		$this->assertSame(strtotime('2010/11/01'), $this->Model->convertDatetime('2010/11/1'));
		$this->assertSame(strtotime('2010/11/01 2:00:00'), $this->Model->convertDatetime('2010/11/1 2:00:00'));
		$this->assertSame(strtotime('2010/11/01'), $this->Model->convertDatetime('２０１０／１１／１'));
		$this->assertFalse($this->Model->convertDatetime('2010/11'));
		$this->assertFalse($this->Model->convertDatetime(time()));
		$this->assertFlase($this->Model->convertDatetime(true));
	}
}
