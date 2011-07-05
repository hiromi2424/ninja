<?php
/* NinjaHtml Test cases generated on: 2011-05-22 08:41:25 : 1306021285*/
App::import('TestSuite', 'Ninja.NinjaHelperTestCase');

class NinjaHtmlHelperTestCase extends NinjaHelperTestCase {

	private $__assetFilterBackup;

	public function startTest($method = null) {
		$this->__assetFilterBackup = Configure::read('Asset.filter');
		parent::startTest($method);
	}

	public function endTest($method = null) {
		Configure::write('Asset.filter', $this->__assetFilterBackup);
		parent::endTest($method);
	}

	public function testImage() {
		$result = $this->NinjaHtml->image('fuga.jpg', array());
		$this->assertNoPattern('/title=/', $result);

		$result = $this->NinjaHtml->image('fuga.jpg', array('alt' => 'one'));
		$this->assertPattern('/title="one"/', $result);

		$result = $this->NinjaHtml->image('fuga.jpg', array('title' => 'one'));
		$this->assertPattern('/alt="one"/', $result);

		$result = $this->NinjaHtml->image('fuga.jpg', array('alt' => 'one', 'title' => 'two'));
		$this->assertPattern('/alt="one"/', $result);
		$this->assertPattern('/title="two"/', $result);

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
		$this->assertPattern('|ccss/hoge.css|', $result);

		$result = $this->NinjaHtml->script('hoge', array('once' => false));
		$this->assertPattern('|cjs/hoge.js|', $result);

		$result = $this->NinjaHtml->css('hoge', null, array('assetFilter' => false));
		$this->assertPattern('|[^c]css/hoge.css|', $result);

		$result = $this->NinjaHtml->script('hoge', array('assetFilter' => false, 'once' => false));
		$this->assertPattern('|[^c]js/hoge.js|', $result);

		Configure::write('Asset.filter', null);

		$result = $this->NinjaHtml->css('hoge');
		$this->assertPattern('|[^c]css/hoge.css|', $result);

		$result = $this->NinjaHtml->script('hoge', array('once' => false));
		$this->assertPattern('|[^c]js/hoge.js|', $result);
	}

}
