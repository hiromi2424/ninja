<?php

App::import('Core', 'Router');
App::import('Lib', 'Ninja.route' . DS . 'AutoSlugRoute');
App::import('Lib', 'Ninja.test' . DS . 'NinjaTestCase');

class AutoSlugRouteTestPost extends CakeTestModel {

	public $name = 'AutoSlugRouteTestPost';
	public $alias = 'Post';
	public $useTable = 'posts';

}

class AutoSlugRouteTest extends NinjaTestCase {

	protected $_routingBackup;
	protected $_cacheBackup;

	public $fixtures = array(
		'core.post',
		'core.author',
	);

	public function startCase() {
		parent::startCase();
		$this->_routingBackup = Configure::read('Routing');
		$this->_cacheBackup = Configure::read('Cache');

		Configure::write('Routing', null);
		Configure::write('Cache.disable', false);

		ClassRegistry::init(array('class' => 'AutoSlugRouteTestPost', 'alias' => 'Post'));
	}

	public function endCase() {
		Configure::write('Routing', $this->_routingBackup);
		Configure::write('Cache', $this->_cacheBackup);
		parent::endCase();
	}

	public function endTest($method = null) {
		AutoSlugRoute::clearCache('AutoSlugRouteTestPost');
		parent::endTest($method);
	}

	public function testParse() {
		$route = new AutoSlugRoute('/post/:title', array('controller' => 'posts', 'action' => 'view'));

		$result = $route->parse('/post/First Post');
		$this->assertEqual($result['controller'], 'posts');
		$this->assertEqual($result['action'], 'view');
		$this->assertEqual($result['pass'], array(1));

		// cached
		$result = $route->parse('/post/First Post');
		$this->assertEqual($result['controller'], 'posts');
		$this->assertEqual($result['action'], 'view');
		$this->assertEqual($result['pass'], array(1));

		$Post = ClassRegistry::init('Post');
		$Post->save($Post->create(array(
			'author_id' => 1,
			'title' => 'New Title',
		)));

		$result = $route->parse('/post/New Title');
		$this->assertEqual($result['controller'], 'posts');
		$this->assertEqual($result['action'], 'view');
		$this->assertEqual($result['pass'], array($Post->getInsertId()));


		$route = new AutoSlugRoute('/post/:title/*', array('controller' => 'posts', 'action' => 'view'));

		$result = $route->parse('/post/First Post/2');
		$this->assertEqual($result['controller'], 'posts');
		$this->assertEqual($result['action'], 'view');
		$this->assertEqual($result['pass'], array(1));
		$this->assertEqual($result['_args_'], '2');


		$result = $route->parse('/post/invalid');
		$this->assertFalse($result);

		$result = $route->parse('/invalid');
		$this->assertFalse($result);
	}

	public function testMatch() {
		$route = new AutoSlugRoute('/post/:title', array('controller' => 'posts', 'action' => 'view'));

		$result = $route->match(array('controller' => 'posts', 'action' => 'view', 1));
		$this->assertEqual($result, '/post/' . rawurlencode('First Post'));

		// cached
		$result = $route->match(array('controller' => 'posts', 'action' => 'view', 1));
		$this->assertEqual($result, '/post/' . rawurlencode('First Post'));

		$Post = ClassRegistry::init('Post');
		$Post->save($Post->create(array(
			'author_id' => 1,
			'title' => 'New Title',
		)));
		$result = $route->match(array('controller' => 'posts', 'action' => 'view', $Post->getInsertId()));
		$this->assertEqual($result, '/post/' . rawurlencode('New Title'));


		$route = new AutoSlugRoute('/post/:title/*', array('controller' => 'posts', 'action' => 'view'));

		$result = $route->match(array('controller' => 'posts', 'action' => 'view', 1, 2));
		$this->assertEqual($result, sprintf('/post/%s/2', rawurlencode('First Post')));


		$result = $route->match(array('controller' => 'posts', 'action' => 'view', -1));
		$this->assertFalse($result);

		$result = $route->match(array('controller' => 'posts', 'action' => 'index'));
		$this->assertFalse($result);
	}

	public function testCacheControl() {
		$route = new AutoSlugRoute('/post/:title/*', array('controller' => 'posts', 'action' => 'view'));
		$route->parse('/post/invalid');

		$this->assertPattern('/^post.+title$/i', AutoSlugRoute::getCacheName('AutoSlugRouteTestPost'));

		$result = AutoSlugRoute::readCache('AutoSlugRouteTestPost');
		$expected = array(
			1 => 'First Post',
			2 => 'Second Post',
			3 => 'Third Post',
		);
		$this->assertEqual($result, $expected);

		$data = array(
			1 => 'Post1',
			2 => 'Post2',
			3 => 'Post3',
		);
		$this->assertTrue(AutoSlugRoute::storeCache('AutoSlugRouteTestPost', $data));
		$result = AutoSlugRoute::readCache('AutoSlugRouteTestPost');
		$this->assertEqual($result, $data);

		$this->assertTrue(AutoSlugRoute::clearCache('AutoSlugRouteTestPost'));
		$result = AutoSlugRoute::readCache('AutoSlugRouteTestPost');
		$this->assertFalse($result);
	}

}