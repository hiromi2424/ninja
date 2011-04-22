<?php
/* CommonValidation Test cases generated on: 2010-11-23 09:11:17 : 1290472577*/
App::import('Lib', 'Ninja.test/AppBehaviorTestCase');

class CommonValidationBehaviorMockModel extends Model {
	public $actsAs = array('Ninja.CommonValidation' => array('user_class' => 'CommonValidationBehaviorUser'));
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

class CommonValidationBehaviorTestCase extends AppBehaviorTestCase {
	public $fixtures = array(
		'plugin.ninja.common_validation_behavior_mock_model',
		'plugin.ninja.common_validation_behavior_belonged',
		'plugin.ninja.common_validation_behavior_user',
	);

	public function testIsCurrentUser() {
		Configure::write('CurrentUser.id', null);
		$this->assertFalse($this->Model->isCurrentUser(array('common_validation_behavior_user_id' => 1)));
		$this->assertFalse($this->Model->isCurrentUser(array('common_validation_behavior_user_id' => null)));

		Configure::write('CurrentUser.id', 1);
		$this->assertFalse($this->Model->isCurrentUser(array('common_validation_behavior_user_id' => null)));
		$this->assertFalse($this->Model->isCurrentUser(array('common_validation_behavior_user_id' => 3)));
		$this->assertTrue($this->Model->isCurrentUser(array('common_validation_behavior_user_id' => 1)));

		Configure::write('CurrentUser.id', 10);
		$this->assertFalse($this->Model->isCurrentUser(array('common_validation_behavior_user_id' => 10)));
	}

	public function testIsNotCurrentUser() {
		Configure::write('CurrentUser.id', 1);
		$this->assertTrue($this->Model->isNotCurrentUser(array('common_validation_behavior_user_id' => 3)));
		$this->assertFalse($this->Model->isNotCurrentUser(array('common_validation_behavior_user_id' => 1)));
	}

	public function testCurrentUserHas() {
		Configure::write('CurrentUser.id', null);
		$this->assertFalse($this->Model->currentUserHas(array('common_validation_behavior_belonged_id' => null)));
		$this->assertFalse($this->Model->currentUserHas(array('common_validation_behavior_belonged_id' => 1)));

		Configure::write('CurrentUser.id', 2);
		$this->expectError();
		$this->assertFalse($this->Model->currentUserHas(array('not_valid_id' => 1)));
		$this->assertTrue($this->Model->currentUserHas(array('common_validation_behavior_belonged_id' => 1)));

		Configure::write('CurrentUser.id', 1);
		$this->assertFalse($this->Model->currentUserHas(array('common_validation_behavior_belonged_id' => 1)));
	}

	public function testCurrentUserHasNot() {
		Configure::write('CurrentUser.id', 2);
		$this->assertFalse($this->Model->currentUserHasnot(array('common_validation_behavior_belonged_id' => 1)));

		Configure::write('CurrentUser.id', 1);
		$this->assertTrue($this->Model->currentUserHasNot(array('common_validation_behavior_belonged_id' => 1)));
	}

	public function testHasThis() {
		$this->Model->id = false;
		$this->assertFalse($this->Model->hasThis(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = array(1, 2);
		$this->assertFalse($this->Model->hasThis(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = 1;
		$this->assertFalse($this->Model->hasThis(array('common_validation_behavior_belonged_id' => null)));
		$this->assertFalse($this->Model->hasThis(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = 2;
		$this->assertFalse($this->Model->hasThis(array('common_validation_behavior_belonged_id' => 2)));
		$this->assertTrue($this->Model->hasThis(array('common_validation_behavior_belonged_id' => 1)));

		$this->assertFalse($this->Model->hasThis(array('not_valid_id' => 1)));
	}

	public function testHasNotThis() {
		$this->assertFalse($this->Model->hasNotThis(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = array(1, 2);
		$this->assertFalse($this->Model->hasNotThis(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = 1;
		$this->assertFalse($this->Model->hasNotThis(array('common_validation_behavior_belonged_id' => null)));
		$this->assertTrue($this->Model->hasNotThis(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = 2;
		$this->assertTrue($this->Model->hasNotThis(array('common_validation_behavior_belonged_id' => 2)));
		$this->assertFalse($this->Model->hasNotThis(array('common_validation_behavior_belonged_id' => 1)));

		$this->assertFalse($this->Model->hasNotThis(array('not_valid_id' => 1)));
	}

	public function testThisHas() {
		$this->Model->id = false;
		$this->assertFalse($this->Model->thisHas(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = array(1, 2);
		$this->assertFalse($this->Model->thisHas(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = 3;
		$this->assertFalse($this->Model->thisHas(array('common_validation_behavior_belonged_id' => null)));
		$this->assertFalse($this->Model->thisHas(array('common_validation_behavior_belonged_id' => 5)));

		$this->Model->id = 4;
		$this->assertFalse($this->Model->thisHas(array('common_validation_behavior_belonged_id' => null)));
		$this->assertTrue($this->Model->thisHas(array('common_validation_behavior_belonged_id' => 5)));

		$this->expectError();
		$this->assertFalse($this->Model->thisHas(array('not_valid_id' => 1)));
	}

	public function testThisHasNot() {
		$this->Model->id = false;
		$this->assertFalse($this->Model->thisHasNot(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = array(1, 2);
		$this->assertFalse($this->Model->thisHasNot(array('common_validation_behavior_belonged_id' => 1)));

		$this->Model->id = 3;
		$this->assertFalse($this->Model->thisHasNot(array('common_validation_behavior_belonged_id' => null)));
		$this->assertTrue($this->Model->thisHasNot(array('common_validation_behavior_belonged_id' => 5)));

		$this->Model->id = 4;
		$this->assertFalse($this->Model->thisHasNot(array('common_validation_behavior_belonged_id' => null)));
		$this->assertFalse($this->Model->thisHasNot(array('common_validation_behavior_belonged_id' => 5)));

		$this->expectError();
		$this->assertFalse($this->Model->thisHasNot(array('not_valid_id' => 1)));
	}

	public function testNotExists() {
		$this->Model->set(array('id' => 1));
		$this->assertFalse($this->Model->notExists());

		$this->Model->set(array('id' => 100));
		$this->assertTrue($this->Model->notExists());
	}

	public function testExistsForeign() {
		$this->assertFalse($this->Model->existsForeign(array('common_validation_behavior_belonged_id' => null)));
		$this->assertFalse($this->Model->existsForeign(array('common_validation_behavior_belonged_id' => 100)));
		$this->assertTrue($this->Model->existsForeign(array('common_validation_behavior_belonged_id' => 5)));

		$this->expectError();
		$this->assertFalse($this->Model->existsForeign(array('not_valid_id' => 1)));
	}

	public function testNotExistsForeign() {
		$this->assertFalse($this->Model->notExistsForeign(array('common_validation_behavior_belonged_id' => null)));
		$this->assertFalse($this->Model->notExistsForeign(array('common_validation_behavior_belonged_id' => 5)));
		$this->assertTrue($this->Model->notExistsForeign(array('common_validation_behavior_belonged_id' => 100)));

		$this->expectError();
		$this->assertFalse($this->Model->notExistsForeign(array('not_valid_id' => 1)));
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
		$this->assertTrue(!!$this->Model->save($this->Model->create($data)));
		$this->assertFalse($this->Model->save($this->Model->create($data)));
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
	}

	public function testGenerateFormatedDatetime() {
		$this->Behavior = new CommonValidationBehavior;
		$result = $this->Behavior->generateFormatedDatetime(strtotime('2010-09-26 14:57:38'));
		$expected = array('year' => '2010', 'month' => '09', 'day' => '26', 'hour' => '14', 'min' => '57', 'sec' => '38');
		$this->assertEqual($result, $expected);

		$result = $this->Model->deconstruct('created', $result);
		$expected = '2010-09-26 14:57:38';
		$this->assertEqual($result, $expected);
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
		$this->assertEqual($this->Model->convertDatetime('2010/11/1'), 1288537200);
		$this->assertEqual($this->Model->convertDatetime('2010/11/1 2:00:00'), 1288544400);
		$this->assertEqual($this->Model->convertDatetime('２０１０／１１／１'), 1288537200);
		$this->assertEqual($this->Model->convertDatetime('2010/11'), false);
		$this->assertEqual($this->Model->convertDatetime(time()), false);
		$this->assertEqual($this->Model->convertDatetime(true), false);
	}
}
