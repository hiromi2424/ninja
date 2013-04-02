<?php
/* TransactionService Test cases generated on: 2011-09-15 18:44:46 : 1316079886*/

App::uses('NinjaModelTestCase', 'Ninja.TestSuite');
App::import('Datasource', 'DboSource', false);

App::import('Model', 'Ninja.BaseService');

class TransactionServiceTest extends NinjaModelTestCase {

	public static $lambdaArguments;
	public static $lambdaResult = true;
	public $lambda;

	public static $mockClasses = array();
	public $MockDboSource;
	public $MockBaseService;

	public function setUp() {
		parent::setUp();
		if (empty(self::$mockClasses)) {
			self::$mockClasses = array(
				'DboSource' => $this->getMockClass('DboSource', array('begin', 'commit', 'rollback', 'connect')),
				'BaseService' => $this->getMockClass('BaseService', array('testMethod')),
			);
		}

		App::uses(self::$mockClasses['DboSource'], 'Model/Datasource');
		$this->MockDboSource = ConnectionManager::create('_transactionServiceMock', array(
			'datasource' => self::$mockClasses['DboSource'],
		));

		$this->MockBaseService = ClassRegistry::init(self::$mockClasses['BaseService']);
		$this->TransactionService->setDataSources('_transactionServiceMock')->setService($this->MockBaseService)->logException = false;
		$this->lambda = create_function('', '
			TransactionServiceTest::$lambdaArguments = func_get_args();
			return TransactionServiceTest::$lambdaResult;
		');
	}

	public function tearDown() {	
		ConnectionManager::drop('_transactionServiceMock');
		parent::tearDown();
	}

	public function testLambda() {
		$this->MockDboSource->expects($this->once())->method('begin');
		$this->MockDboSource->expects($this->once())->method('commit');
		$this->MockDboSource->expects($this->never())->method('rollback');
		$result = $this->TransactionService->lambda($this->lambda);
		$this->assertTrue($result);
		$this->assertInstanceOf(self::$mockClasses['BaseService'], self::$lambdaArguments[0]);
	}

	public function testLambdaFailed() {
		$this->MockDboSource->expects($this->once())->method('begin');
		$this->MockDboSource->expects($this->never())->method('commit');
		$this->MockDboSource->expects($this->once())->method('rollback');
		self::$lambdaResult = false;
		$result = $this->TransactionService->lambda($this->lambda);
		$this->assertFalse($result);
	}

	public function testMagicCall() {
		$this->TransactionService->catchTry = false;
		$this->MockDboSource->expects($this->once())->method('begin');
		$this->MockDboSource->expects($this->once())->method('commit');
		$this->MockDboSource->expects($this->never())->method('rollback');
		$this->MockBaseService->expects($this->once())
			->method('testMethod')
			->will($this->returnValue(true));
		$result = $this->TransactionService->testMethod();
		$this->assertTrue($result);
	}

	public function testMagicCallFailed() {
		$this->TransactionService->catchTry = false;
		$this->MockDboSource->expects($this->once())->method('begin');
		$this->MockDboSource->expects($this->never())->method('commit');
		$this->MockDboSource->expects($this->once())->method('rollback');
		$this->MockBaseService->expects($this->once())
			->method('testMethod')
			->will($this->returnValue(false));
		$result = $this->TransactionService->testMethod();
		$this->assertFalse($result);
	}

	public function testMagicCallThrowException() {
		$this->TransactionService->catchTry = true;
		$this->MockDboSource->expects($this->once())->method('begin');
		$this->MockDboSource->expects($this->never())->method('commit');
		$this->MockDboSource->expects($this->once())->method('rollback');
		$this->MockBaseService->expects($this->once())
			->method('testMethod')
			->will($this->throwException(new Exception));
		$result = $this->TransactionService->testMethod();
		$this->assertFalse($result);
	}

	public function testInvalidMagicCall() {
		$this->MockDboSource->expects($this->never())->method('begin');
		$this->MockDboSource->expects($this->never())->method('commit');
		$this->MockDboSource->expects($this->never())->method('rollback');
		$this->setExpectedException('BadMethodCallException');
		$this->TransactionService->notDefinedMethod();
	}

}
