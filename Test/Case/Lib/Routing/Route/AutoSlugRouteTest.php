<?php

App::uses('Router', 'Routing');
App::uses('CakeRoute', 'Routing/Route');
App::uses('AutoSlugRoute', 'Ninja.Routing/Route');
App::import('TestSuite', 'Ninja.NinjaTestCase');

class AutoSlugRouteTestPost extends CakeTestModel {

	public $alias = 'Post';
	public $useTable = 'posts';

}

class AutoSlugRouteTest extends NinjaTestCase {

	protected static $_routingBackup;
	protected static $_cacheBackup;

	public $fixtures = array(
		'core.post',
		'core.author',
	);

	public static function setupBeforeClass() {
		self::$_routingBackup = Configure::read('Routing');
		self::$_cacheBackup = Configure::read('Cache');

		Configure::write('Routing', null);
		Configure::write('Cache.disable', false);

		ClassRegistry::init(array('class' => 'AutoSlugRouteTestPost', 'alias' => 'Post'));
	}

	public static function tearDownAfterClass() {
		Configure::write('Routing', self::$_routingBackup);
		Configure::write('Cache', self::$_cacheBackup);
	}

	public function setUp() {
		ClassRegistry::init(array('class' => 'AutoSlugRouteTestPost', 'alias' => 'Post'));
		parent::setUp();
	}

	public function tearDown() {
		AutoSlugRoute::clearCache('AutoSlugRouteTestPost');
		AutoSlugRoute::clearCache('AutoSlugRouteTestPostBody');
		parent::tearDown();
	}

	public function testParse() {
		$route = new AutoSlugRoute('/post/:title', array('controller' => 'posts', 'action' => 'view'));

		$result = $route->parse('/post/First Post');
		$this->assertEquals('posts', $result['controller']);
		$this->assertEquals('view', $result['action']);
		$this->assertEquals(array(1), $result['pass']);

		// cached
		$result = $route->parse('/post/First Post');
		$this->assertEquals('posts', $result['controller']);
		$this->assertEquals('view', $result['action']);
		$this->assertEquals(array(1), $result['pass']);

		$Post = ClassRegistry::init('Post');
		$Post->save($Post->create(array(
			'author_id' => 1,
			'title' => 'New Title',
		)));

		$result = $route->parse('/post/New Title');
		$this->assertEquals('posts', $result['controller']);
		$this->assertEquals('view', $result['action']);
		$this->assertEquals(array($Post->getInsertId()), $result['pass']);


		$route = new AutoSlugRoute('/post/:title/*', array('controller' => 'posts', 'action' => 'view'));

		$result = $route->parse('/post/First Post/2');
		$this->assertEquals('posts', $result['controller']);
		$this->assertEquals('view', $result['action']);
		$this->assertEquals(array(1, 2), $result['pass']);


		$result = $route->parse('/post/invalid');
		$this->assertFalse($result);

		$result = $route->parse('/invalid');
		$this->assertFalse($result);
	}

	public function testMatch() {
		$route = new AutoSlugRoute('/post/:title', array('controller' => 'posts', 'action' => 'view'));

		$result = $route->match(array('controller' => 'posts', 'action' => 'view', 1));
		$this->assertEquals('/post/' . rawurlencode('First Post'), $result);

		// cached
		$result = $route->match(array('controller' => 'posts', 'action' => 'view', 1));
		$this->assertEquals('/post/' . rawurlencode('First Post'), $result);

		$Post = ClassRegistry::init('Post');
		$Post->save($Post->create(array(
			'author_id' => 1,
			'title' => 'New Title',
		)));
		$result = $route->match(array('controller' => 'posts', 'action' => 'view', $Post->getInsertId()));
		$this->assertEquals('/post/' . rawurlencode('New Title'), $result);


		$route = new AutoSlugRoute('/post/:title/*', array('controller' => 'posts', 'action' => 'view'));

		$result = $route->match(array('controller' => 'posts', 'action' => 'view', 1, 2));
		$this->assertEquals(sprintf('/post/%s/2', rawurlencode('First Post')), $result);


		$result = $route->match(array('controller' => 'posts', 'action' => 'view', -1));
		$this->assertFalse($result);

		$result = $route->match(array('controller' => 'posts', 'action' => 'index'));
		$this->assertFalse($result);
	}

	public function testCacheControl() {
		$route = new AutoSlugRoute('/post/:title/*', array('controller' => 'posts', 'action' => 'view'));
		$route->parse('/post/invalid');

		$this->assertRegExp('/^post.+title$/i', AutoSlugRoute::getCacheName('AutoSlugRouteTestPost'));

		$result = AutoSlugRoute::readCache('AutoSlugRouteTestPost');
		$expected = array(
			1 => 'First Post',
			2 => 'Second Post',
			3 => 'Third Post',
		);
		$this->assertEquals($expected, $result);

		$data = array(
			1 => 'Post1',
			2 => 'Post2',
			3 => 'Post3',
		);
		$this->assertTrue(AutoSlugRoute::storeCache('AutoSlugRouteTestPost', $data));
		$result = AutoSlugRoute::readCache('AutoSlugRouteTestPost');
		$this->assertEquals($data, $result);

		$this->assertTrue(AutoSlugRoute::clearCache('AutoSlugRouteTestPost'));
		$result = AutoSlugRoute::readCache('AutoSlugRouteTestPost');
		$this->assertFalse($result);

		$route->parse('/post/invalid');
		$route = new AutoSlugRoute('/post/body/:body/*', array('controller' => 'posts', 'action' => 'view'), array(
			'named' => 'body',
			'display' => 'body',
			'identity' => 'AutoSlugRouteTestPostBody',
		));
		$route->parse('/post/body/invalid');

		$result = AutoSlugRoute::readCache('AutoSlugRouteTestPost');
		$this->assertTrue(!empty($result));
		$result = AutoSlugRoute::readCache('AutoSlugRouteTestPostBody');
		$this->assertTrue(!empty($result));

		$this->assertTrue(AutoSlugRoute::clearCacheAll());

		$result = AutoSlugRoute::readCache('AutoSlugRouteTestPost');
		$this->assertTrue(empty($result));
		$result = AutoSlugRoute::readCache('AutoSlugRouteTestPostBody');
		$this->assertTrue(empty($result));

		$this->assertFalse(AutoSlugRoute::clearCacheAll());
	}

}