<?php

App::uses('Router', 'Routing');
App::uses('CakeRoute', 'Routing/Route');
App::uses('MultiSlugRoute', 'Ninja.Routing/Route');
App::import('TestSuite', 'Ninja.NinjaTestCase');

class MultiSlugRouteTestPost extends CakeTestModel {

	public $alias = 'Post';
	public $useTable = 'posts';

	public $belongsTo = array(
		'Author',
	);

}

class MultiSlugRouteTest extends NinjaTestCase {

	protected static $_routingBackup;
	protected static $_cacheBackup;

	public $fixtures = array(
		'core.post',
		'core.author',
	);

	public $excludeCase = 'testScalability';

	public static function setupBeforeClass() {
		self::$_routingBackup = Configure::read('Routing');
		self::$_cacheBackup = Configure::read('Cache');

		Configure::write('Routing', null);
		Configure::write('Cache.disable', false);
	}

	public static function tearDownAfterClass() {
		Configure::write('Routing', self::$_routingBackup);
		Configure::write('Cache', self::$_cacheBackup);
	}

	public function startTest($method) {
		parent::startTest($method);
		ClassRegistry::init(array('class' => 'MultiSlugRouteTestPost', 'alias' => 'Post'));
	}

	public function endTest($method) {
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
		$this->assertEquals(sprintf('/post/%s/%s', rawurlencode('mariano'), rawurlencode('First Post')), $result);

		// cached
		$result = $route->match(array('controller' => 'posts', 'action' => 'view', 1));
		$this->assertEquals(sprintf('/post/%s/%s', rawurlencode('mariano'), rawurlencode('First Post')), $result);

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
		$this->assertEquals(sprintf('/post/%s/%s', rawurlencode('Hiromi'), rawurlencode('New Post')), $result);


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
		$this->assertEquals('posts', $result['controller']);
		$this->assertEquals('view', $result['action']);
		$this->assertEquals(array(1), $result['pass']);

		// cached
		$result = $route->parse('/post/mariano/First Post');
		$this->assertEquals('posts', $result['controller']);
		$this->assertEquals('view', $result['action']);
		$this->assertEquals(array(1), $result['pass']);

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
		$this->assertEquals('posts', $result['controller']);
		$this->assertEquals('view', $result['action']);
		$this->assertEquals(array(10), $result['pass']);

	}

	public function testMapping() {
		$route = $this->_createInstance();
		$route->parse('/post/mariano/First Post');
		$mapParse = MultiSlugRoute::readCache('Post');

		MultiSlugRoute::clearCacheAll();

		$route = $this->_createInstance();
		$route->match(array('controller' => 'posts', 'action' => 'view', 1));
		$mapMatch = MultiSlugRoute::readCache('Post');
		$this->assertEquals($mapMatch, $mapParse);
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
		$this->assertEquals('posts', $result['controller']);
		$this->assertEquals('view', $result['action']);
		$this->assertEquals(array(1), $result['pass']);

		$result = $route->match(array('controller' => 'posts', 'action' => 'view', 1));
		$this->assertEquals(sprintf('/post/%s/%s', rawurlencode('First Post'), rawurlencode('author;mariano')), $result);

	}

	public function testGreedy() {
		$route = $this->_createInstance('/post/:user/:title/*');

		$result = $route->parse('/post/mariano/First Post/2');
		$this->assertEquals('posts', $result['controller']);
		$this->assertEquals('view', $result['action']);
		$this->assertEquals(array(1, 2), $result['pass']);

		$result = $route->match(array('controller' => 'posts', 'action' => 'view', 1, 2));
		$this->assertEquals(sprintf('/post/%s/%s/2', rawurlencode('mariano'), rawurlencode('First Post'), rawurlencode('2')), $result);

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
		$this->assertEquals($expected, $result);
	}

	public function testCacheControll() {

		$this->assertNull(MultiSlugRoute::clearCacheAll());
		$this->assertNull(MultiSlugRoute::clearCache('Post'));
		$this->assertNull(MultiSlugRoute::readCache('Post'));
		$this->assertNull(MultiSlugRoute::storeCache('Post', 'data'));

		$route = $this->_createInstance();

		$result = MultiSlugRoute::getCacheName('Post');
		$this->assertEquals('posts', $result);

		$route->parse('/post/mariano/First Post');
		$route = $this->_createInstance();

		$result = MultiSlugRoute::readCache('Post');
		$id = key($result);
		$slugs = current($result);
		$this->assertEquals(1, $id);
		$this->assertEquals('mariano', $slugs['user']);
		$this->assertEquals('First Post', $slugs['title']);

		$this->assertTrue(MultiSlugRoute::storeCache('Post', 'test'));
		$this->assertEquals('test', MultiSlugRoute::readCache('Post'));
		$this->assertTrue(MultiSlugRoute::clearCache('Post'));
		$this->assertEquals(array(), MultiSlugRoute::readCache('Post'));
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

		$this->assertEquals('posts', $result['controller']);
		$this->assertEquals('view', $result['action']);
		$this->assertEquals(array(100), $result['pass']);

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