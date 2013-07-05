<?php
/* NinjaHtml Test cases generated on: 2011-05-22 08:41:25 : 1306021285*/
App::import('TestSuite', 'Ninja.NinjaHelperTestCase');

class NinjaHtmlHelperTest extends NinjaHelperTestCase {

	private $__assetFilterBackup;

	public function setUp() {
		$this->__assetFilterBackup = Configure::read('Asset.filter');
		parent::setUp();
	}

	public function tearDown() {
		Configure::write('Asset.filter', $this->__assetFilterBackup);
		parent::tearDown();
	}

	public function testImage() {
		$result = $this->NinjaHtml->image('fuga.jpg', array());
		$this->assertNoPattern('/title=/', $result);

		$result = $this->NinjaHtml->image('fuga.jpg', array('alt' => 'one'));
		$this->assertRegExp('/title="one"/', $result);

		$result = $this->NinjaHtml->image('fuga.jpg', array('title' => 'one'));
		$this->assertRegExp('/alt="one"/', $result);

		$result = $this->NinjaHtml->image('fuga.jpg', array('alt' => 'one', 'title' => 'two'));
		$this->assertRegExp('/alt="one"/', $result);
		$this->assertRegExp('/title="two"/', $result);

		$this->loadHelper(array(
			'altEqualTitle' => false,
		));

		$result = $this->NinjaHtml->image('fuga.jpg', array('alt' => 'one'));
		$this->assertNoPattern('/title="one"/', $result);

		$result = $this->NinjaHtml->image('fuga.jpg', array('title' => 'one'));
		$this->assertNoPattern('/alt="one"/', $result);
	}

	public function testAssetMethods() {
		Configure::write('Asset.filter.css', 'test_css.php');
		Configure::write('Asset.filter.js', 'test_js.php');

		$result = $this->NinjaHtml->css('hoge');
		$this->assertRegExp('|ccss/hoge.css|', $result);

		$result = $this->NinjaHtml->script('hoge', array('once' => false));
		$this->assertRegExp('|cjs/hoge.js|', $result);

		$result = $this->NinjaHtml->css('hoge', null, array('assetFilter' => false));
		$this->assertRegExp('|[^c]css/hoge.css|', $result);

		$result = $this->NinjaHtml->script('hoge', array('assetFilter' => false, 'once' => false));
		$this->assertRegExp('|[^c]js/hoge.js|', $result);

		Configure::write('Asset.filter', null);

		$result = $this->NinjaHtml->css('hoge');
		$this->assertRegExp('|[^c]css/hoge.css|', $result);

		$result = $this->NinjaHtml->script('hoge', array('once' => false));
		$this->assertRegExp('|[^c]js/hoge.js|', $result);
	}

}
