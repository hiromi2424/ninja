<?php
/* TransactionService Test cases generated on: 2011-09-15 18:44:46 : 1316079886*/

App::import('Lib', 'Ninja.test' . DS . 'NinjaModelTestCase');
App::import('Datasource', 'DboSource', false);

App::import('Model', 'Ninja.BaseService');
Mock::generate('BaseService', null, array('testMethod'));

$code = Mock::generate('DboSource');

class TransactionServiceTestCase extends NinjaModelTestCase {

	public static $lambdaArguments;
	public static $lambdaResult = true;
	public $lambda;

	public $MockDboSource;
	public $MockBaseService;

	public $fixtures = false;

	public function startCase() {
		parent::startCase();
	}

	public function startTest($method = null) {
		parent::startTest($method);

		$this->MockDboSource = ConnectionManager::create('_transactionServiceMock', array(
			'datasource' => 'MockDboSource',
		));
		/* 
		 * replace instance if already created. this is workaround failing expecting counts for mock.
		 * this workaround will not be needed since ConnectionManager::drop() was implemented in 2.0.
		 */
		if (null === $this->MockDboSource) {
			$dboReference =& ConnectionManager::getDataSource('_transactionServiceMock');
			$dboReference = new MockDboSource();
			$this->MockDboSource = $dboReference;
		}

		$this->MockBaseService = ClassRegistry::init('MockBaseService');
		$this->TransactionService->setDataSources('_transactionServiceMock')->setService($this->MockBaseService)->logException = false;
		$this->lambda = create_function('', '
			TransactionServiceTestCase::$lambdaArguments = func_get_args();
			return TransactionServiceTestCase::$lambdaResult;
		');
	}

	public function endTest($method = null) {
		$this->MockDboSource = $this->MockBaseService = null;
		parent::endTest($method);
	}

	public function testLambda() {
		$this->MockDboSource->expectOnce('begin');
		$this->MockDboSource->expectOnce('commit');
		$this->MockDboSource->expectNever('rollback');
		$result = $this->TransactionService->lambda($this->lambda);
		$this->assertTrue($result);
		$this->assertIsA(self::$lambdaArguments[0], 'MockBaseService');
	}

	public function testLambdaFailed() {
		$this->MockDboSource->expectOnce('begin');
		$this->MockDboSource->expectNever('commit');
		$this->MockDboSource->expectOnce('rollback');
		self::$lambdaResult = false;
		$result = $this->TransactionService->lambda($this->lambda);
		$this->assertFalse($result);
	}

	public function testMagicCall() {
		$this->TransactionService->catchTry = false;
		$this->MockDboSource->expectOnce('begin');
		$this->MockDboSource->expectOnce('commit');
		$this->MockDboSource->expectNever('rollback');
		$this->MockBaseService->setReturnValue('testMethod', true);
		$result = $this->TransactionService->testMethod();
		$this->assertTrue($result);
	}

	public function testMagicCallFailed() {
		$this->TransactionService->catchTry = false;
		$this->MockDboSource->expectOnce('begin');
		$this->MockDboSource->expectNever('commit');
		$this->MockDboSource->expectOnce('rollback');
		$this->MockBaseService->setReturnValue('testMethod', false);
		$result = $this->TransactionService->testMethod();
		$this->assertFalse($result);
	}

	public function testMagicCallThrowException() {
		$this->TransactionService->catchTry = true;
		$this->MockDboSource->expectOnce('begin');
		$this->MockDboSource->expectNever('commit');
		$this->MockDboSource->expectOnce('rollback');
		$this->MockBaseService->throwOn('testMethod');
		$result = $this->TransactionService->testMethod();
		$this->assertFalse($result);
	}

	public function testInvalidMagicCall() {
		$this->MockDboSource->expectNever('begin');
		$this->MockDboSource->expectNever('commit');
		$this->MockDboSource->expectNever('rollback');
		$this->expectException('BadMethodCallException');
		$this->TransactionService->notDefinedMethod();
	}

}
