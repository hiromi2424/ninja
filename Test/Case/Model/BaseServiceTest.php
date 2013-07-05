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

		$this->assertInstanceOf('BaseServiceTestModel', $this->BaseService->BaseServiceTestModel);
		$this->assertInstanceOf('TransactionService', $this->BaseService->Transaction);

	}

}
