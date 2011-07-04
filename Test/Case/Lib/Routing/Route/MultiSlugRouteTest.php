<?php

App::import('Core', 'Router');
App::import('Lib', 'Ninja.route' . DS . 'MultiSlugRoute');
App::import('Lib', 'Ninja.test' . DS . 'NinjaTestCase');

class MultiSlugRouteTestPost extends CakeTestModel {

	public $name = 'MultiSlugRouteTestPost';
	public $alias = 'Post';
	public $useTable = 'posts';

	public $belongsTo = 'Author';

}

class MultiSlugRouteTest extends NinjaTestCase {

	protected $_routingBackup;
	protected $_cacheBackup;

	public $fixtures = array(
		'core.post',
		'core.author',
	);

	public $excludeCase = 'testScalability';

	public function startCase() {
		parent::startCase();
		$this->_routingBackup = Configure::read('Routing');
		$this->_cacheBackup = Configure::read('Cache');

		Configure::write('Routing', null);
		Configure::write('Cache.disable', false);

		ClassRegistry::init(array('class' => 'MultiSlugRouteTestPost', 'alias' => 'Post'));
	}

	public function endCase() {
		Configure::write('Routing', $this->_routingBackup);
		Configure::write('Cache', $this->_cacheBackup);
		parent::endCase();
	}

	public function endTest($method = null) {
		MultiSlugRoute::clearCacheAll();
		MultiSlugRoute::flush();
		parent::endTest($method);
	}

	protected function _createInstance($route = null, $defaults = null, $options = null) {
		$defaultRoute = '/post/:user/:title';
		$defaultDefaults = array(
			'controller' => 'posts',
			'action' => 'view',
		);
		$defualtOptions = array(
			'slugs' => array(
				'user' => array(
					'model' => 'Author',
				),
				'title',
			),
		);

		$route = $route === null ? $defaultRoute : $route;
		$defaults = $defaults === null ? $defaultDefaults : $defaults;
		$options = $options === null ? $defualtOptions : $options;

		return new MultiSlugRoute($route, $defaults, $options);
	}

	public function testMatch() {
		$route = $this->_createInstance();

		$result = $route->match(array('controller' => 'posts', 'action' => 'index'));
		$this->assertFalse($result);

		$result = $route->match(array('controller' => 'author', 'action' => 'view', 1));
		$this->assertFalse($result);

		$result = $route->match(array('controller' => 'posts', 'action' => 'view'));
		$this->assertFalse($result);

		$result = $route->match(array('controller' => 'posts', 'action' => 'view', -1));
		$this->assertFalse($result);

		$result = $route->match(array('controller' => 'posts', 'action' => 'view', 1));
		$this->assertEqual($result, sprintf('/post/%s/%s', rawurlencode('mariano'), rawurlencode('First Post')));

		// cached
		$result = $route->match(array('controller' => 'posts', 'action' => 'view', 1));
		$this->assertEqual($result, sprintf('/post/%s/%s', rawurlencode('mariano'), rawurlencode('First Post')));

		ClassRegistry::init('Post')->saveAll(array(
			'Post' => array(
				'id' => 10,
				'title' => 'New Post',
				'body' => 'body',
				'published' => 'Y',
			),
			'Author' => array(
				'id' => 10,
				'user' => 'Hiromi',
				'password' => 'dummy',
			),
		));

		$result = $route->match(array('controller' => 'posts', 'action' => 'view', 10));
		$this->assertEqual($result, sprintf('/post/%s/%s', rawurlencode('Hiromi'), rawurlencode('New Post')));


	}

	public function testParse() {
		$route = $this->_createInstance();

		$result = $route->parse('/invalidUrl');
		$this->assertFalse($result);

		$result = $route->parse('/post/invaliduser/invalidtitle');
		$this->assertFalse($result);

		// only author is valid
		$result = $route->parse('/post/mariano/invalidtitle');
		$this->assertFalse($result);

		// only title is valid
		$result = $route->parse('/post/invaliduser/First Post');
		$this->assertFalse($result);

		$result = $route->parse('/post/mariano/First Post');
		$this->assertEqual($result['controller'], 'posts');
		$this->assertEqual($result['action'], 'view');
		$this->assertEqual($result['pass'], array(1));

		// cached
		$result = $route->parse('/post/mariano/First Post');
		$this->assertEqual($result['controller'], 'posts');
		$this->assertEqual($result['action'], 'view');
		$this->assertEqual($result['pass'], array(1));

		ClassRegistry::init('Post')->saveAll(array(
			'Post' => array(
				'id' => 10,
				'title' => 'New Post',
				'body' => 'body',
				'published' => 'Y',
			),
			'Author' => array(
				'id' => 10,
				'user' => 'Hiromi',
				'password' => 'dummy',
			),
		));

		$result = $route->parse('/post/Hiromi/New Post');
		$this->assertEqual($result['controller'], 'posts');
		$this->assertEqual($result['action'], 'view');
		$this->assertEqual($result['pass'], array(10));

	}

	public function testMapping() {
		$route = $this->_createInstance();
		$route->parse('/post/mariano/First Post');
		$mapParse = MultiSlugRoute::readCache('Post');

		MultiSlugRoute::clearCacheAll();

		$route = $this->_createInstance();
		$route->match(array('controller' => 'posts', 'action' => 'view', 1));
		$mapMatch = MultiSlugRoute::readCache('Post');
		$this->assertEqual($mapParse, $mapMatch);
	}

	public function testCallback() {
		$route = $this->_createInstance('/post/:title/:user', null, array(
			'slugs' => array(
				'title',
				'user' => array(
					'model' => 'Author',
					'callbacks' => array(
						'match' => create_function('$value', 'return "author;" . $value;'),
						'parse' => create_function('$slug', 'return preg_replace("/^author;/", "", $slug);'),
					),
				),
			),
		));

		$result = $route->parse('/post/First Post/author;mariano');
		$this->assertEqual($result['controller'], 'posts');
		$this->assertEqual($result['action'], 'view');
		$this->assertEqual($result['pass'], array(1));

		$result = $route->match(array('controller' => 'posts', 'action' => 'view', 1));
		$this->assertEqual($result, sprintf('/post/%s/%s', rawurlencode('First Post'), rawurlencode('author;mariano')));

	}

	public function testGreedy() {
		$route = $this->_createInstance('/post/:user/:title/*');

		$result = $route->parse('/post/mariano/First Post/2');
		$this->assertEqual($result['controller'], 'posts');
		$this->assertEqual($result['action'], 'view');
		$this->assertEqual($result['pass'], array(1));
		$this->assertEqual($result['_args_'], 2);

		$result = $route->match(array('controller' => 'posts', 'action' => 'view', 1, 2));
		$this->assertEqual($result, sprintf('/post/%s/%s/2', rawurlencode('mariano'), rawurlencode('First Post'), rawurlencode('2')));

	}

	public function testGenerateQuery() {
		$route = $this->_createInstance();

		$result = $route->generateQuery();
		$expected = array(
			'fields' => array(
				ClassRegistry::init('Post')->escapeField('id'),
				ClassRegistry::init('Author')->escapeField('user'),
				ClassRegistry::init('Post')->escapeField('title'),
			),
			'contain' => array(
				'Author',
			),
			'conditions' => array(
			),
		);
		$this->assertEqual($result, $expected);
	}

	public function testCacheControll() {

		$this->assertNull(MultiSlugRoute::clearCacheAll());
		$this->assertNull(MultiSlugRoute::clearCache('Post'));
		$this->assertNull(MultiSlugRoute::readCache('Post'));
		$this->assertNull(MultiSlugRoute::storeCache('Post', 'data'));

		$route = $this->_createInstance();

		$result = MultiSlugRoute::getCacheName('Post');
		$this->assertEqual($result, 'posts');

		$route->parse('/post/mariano/First Post');
		$route = $this->_createInstance();

		$result = MultiSlugRoute::readCache('Post');
		$id = key($result);
		$slugs = current($result);
		$this->assertEqual($id, 1);
		$this->assertEqual($slugs['user'], 'mariano');
		$this->assertEqual($slugs['title'], 'First Post');

		$this->assertTrue(MultiSlugRoute::storeCache('Post', 'test'));
		$this->assertEqual(MultiSlugRoute::readCache('Post'), 'test');
		$this->assertTrue(MultiSlugRoute::clearCache('Post'));
		$this->assertEqual(MultiSlugRoute::readCache('Post'), array());
		$this->assertFalse(MultiSlugRoute::clearCache('Post'));
		$this->assertFalse(MultiSlugRoute::clearCacheAll());
	}

	public function testScalability() {
		$route = $this->_createInstance();

		$Post = ClassRegistry::init('Post');
		$Author = ClassRegistry::init('Author');
		$Post->deleteAll(true);
		$Author->deleteAll(true);

		$recordUnit = 1000;
		$unitAmount = 100;
		$recordsCount = $recordUnit * $unitAmount;
		var_dump("testing $recordsCount records:");

		$start = microtime(true);
		for ($i = 0; $i < $unitAmount; $i++) {
			$save = array();
			for ($j = 1; $j <= $recordUnit; $j++) {
				$n = $i * $recordUnit + $j;
				$save['Author'][$n] = "($n, 'user$n', 'password$n', '2011-11-11 11:11:11', '2011-11-11 11:11:11')";
				$save['Post'][$n] = "($n, 'Post$n', 'body$n', 'Y', $n, '2011-11-11 11:11:11', '2011-11-11 11:11:11')";
			}
			$Author->getDataSource()->insertMulti('authors', array('id', 'user', 'password', 'created', 'updated'), $save['Author']);
			$Post->getDataSource()->insertMulti('posts', array('id', 'title', 'body', 'published', 'author_id', 'created', 'updated'), $save['Post']);
		}
		$end = microtime(true) - $start;
		var_dump(sprintf('inserting records took %.1f msec', 1000.0 * $end));

		$start = microtime(true);
		$result = $route->parse('/post/user100/Post100');
		$end = microtime(true) - $start;
		var_dump(sprintf('non cached parsing took %.1f msec', 1000.0 * $end));

		$this->assertEqual($result['controller'], 'posts');
		$this->assertEqual($result['action'], 'view');
		$this->assertEqual($result['pass'], array(100));

		$start = microtime(true);
		$result = $route->parse('/post/user100/Post100');
		$end = microtime(true) - $start;
		var_dump(sprintf('after 1 cached parsing took %.1f msec', 1000.0 * $end));

		MultiSlugRoute::clearCacheAll();
		/*
		$start = microtime(true);
		for ($i = 1; $i <= $recordsCount; $i++) {
			$startIndivisual = microtime(true);
			$route->parse("/post/user$i/Post$i");
			if ($i === 2 || $i === $n) {
				var_dump(sprintf($i . ': %.1f msec', 1000.0 * (microtime(true) - $startIndivisual)));
			}
		}
		$end = microtime(true) - $start;
		var_dump(sprintf('%.1f msec', 1000.0 * $end));
		var_dump(sprintf('average %.1f msec', 1000.0 * $end / (float)$n));
		*/

		$start = microtime(true);
		for ($i = 1; $i <= $recordsCount; $i++) {
			$route->map(
				"/post/user$i/Post$i",
				array(
					'controller' => 'posts',
					'action' => 'view',
					'pass' => array($i),
					'named' => array(),
				)
			);
		}
		$end = microtime(true) - $start;
		var_dump(sprintf('mapping took %.1f msec', 1000.0 * $end));

		$route = $this->_createInstance();

		$start = microtime(true);
		$result = $route->parse('/post/user100/Post100');
		$end = microtime(true) - $start;
		var_dump(sprintf('after all cached parsing with loading cache took %.1f msec', 1000.0 * $end));
		var_dump(sprintf('loading cache took %.1f msec', 1000.0 * $route->elapsed));

		$start = microtime(true);
		$result = $route->parse('/post/user100/Post100');
		$end = microtime(true) - $start;
		var_dump(sprintf('after all cached parsing took %.1f msec', 1000.0 * $end));
	}

}