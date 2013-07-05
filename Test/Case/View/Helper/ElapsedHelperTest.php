<?php
/* Elapsed Test cases generated on: 2011-05-21 14:06:44 : 1305954404*/
App::import('TestSuite', 'Ninja.NinjaHelperTestCase');

class ElapsedHelperTest extends NinjaHelperTestCase {

	public function testNice() {
		$msec = __d('elapsed', 'msec');
		$sec = __d('elapsed', 'sec');
		$min = __d('elapsed', 'min');
		$hour = __d('elapsed', 'hour');

		$this->assertEquals($this->Elapsed->nice(4227.2246), "1 $hour 10 $min 27.225 $sec");
		$this->assertEquals($this->Elapsed->nice(0.2246), "225 $msec");

		$this->assertEquals("1 $hour, 10 $min, 27.225 $sec", $this->Elapsed->nice(4227.2246, ', '));
		$this->assertEquals($this->Elapsed->nice(4227.2246, array('decimal' => 1)), "1 $hour 10 $min 27.2 $sec");

		$this->assertEquals($this->Elapsed->nice(0.2246, array('msec' => false)), "0.225 $sec");
		$this->assertEquals($this->Elapsed->nice(4227.2246, array('format' => 'h')), "1 $hour");
		$this->assertEquals($this->Elapsed->nice(4227.2246, array('separator' => ':','format' => 'S:M:H')), "27.225 $sec:10 $min:1 $hour");
	}

}
