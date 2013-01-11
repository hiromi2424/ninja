<?php
/* BaseService Test cases generated on: 2011-09-14 22:21:05 : 1316006465*/

App::uses('NinjaModelTestCase', 'Ninja.TestSuite');
App::import('Model', 'Ninja.BaseService');

class TestBaseService extends BaseService {
}

class BaseServiceTestModel extends CakeTestModel {

	public $useTable = false;

}

class BaseServiceTest extends NinjaModelTestCase {

	public $fixtures = false;

	public function testMagickInstantiate() {

		$this->assertIsA($this->BaseService->BaseServiceTestModel, 'BaseServiceTestModel');
		$this->assertIsA($this->BaseService->Transaction, 'TransactionService');

	}

}
