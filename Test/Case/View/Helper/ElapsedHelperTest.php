<?php
/* Elapsed Test cases generated on: 2011-05-21 14:06:44 : 1305954404*/
App::import('TestSuite', 'Ninja.NinjaHelperTestCase');

class ElapsedHelperTestCase extends NinjaHelperTestCase {

	public function testNice() {
		$msec = __d('elapsed', 'msec', true);
		$sec = __d('elapsed', 'sec', true);
		$min = __d('elapsed', 'min', true);
		$hour = __d('elapsed', 'hour', true);

		$this->assertEqual("1 $hour 10 $min 27.225 $sec", $this->Elapsed->nice(4227.2246));
		$this->assertEqual("225 $msec", $this->Elapsed->nice(0.2246));

		$this->assertEqual("1 $hour, 10 $min, 27.225 $sec", $this->Elapsed->nice(4227.2246, ', '));
		$this->assertEqual("1 $hour 10 $min 27.2 $sec", $this->Elapsed->nice(4227.2246, array('decimal' => 1)));

		$this->assertEqual("0.225 $sec", $this->Elapsed->nice(0.2246, array('msec' => false)));
		$this->assertEqual("1 $hour", $this->Elapsed->nice(4227.2246, array('format' => 'h')));
		$this->assertEqual("27.225 $sec:10 $min:1 $hour", $this->Elapsed->nice(4227.2246, array('separator' => ':','format' => 'S:M:H')));
	}

}
