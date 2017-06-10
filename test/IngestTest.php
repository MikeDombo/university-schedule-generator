<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 6/10/2017
 * Time: 10:37 AM
 */

use PHPUnit\Framework\TestCase;

class IngestTest extends TestCase{
	/**
	 * Call protected/private method of a class.
	 *
	 * @param object &$object    Instantiated object that we will run method on.
	 * @param string $methodName Method name to call
	 * @param array  $parameters Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	public function invokeMethod(&$object, $methodName, array $parameters = array())
	{
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}

	public function testColorGenerator(){
		$ingest = new Ingest(new PDO('sqlite:test.sqlite'), $this->makeTestData());

		$color = $this->invokeMethod($ingest, 'generateColor', [[0, 0, 0]]);
		$this->assertInternalType('array', $color);
		$this->assertEquals(3, count($color));
		$this->assertGreaterThanOrEqual(0, $color[0]);
		$this->assertGreaterThanOrEqual(0, $color[1]);
		$this->assertGreaterThanOrEqual(0, $color[2]);
		$color = $this->invokeMethod($ingest, 'generateColor', [[255, 255, 255]]);
		$this->assertInternalType('array', $color);
		$this->assertEquals(3, count($color));
		$this->assertGreaterThanOrEqual(127, $color[0]);
		$this->assertGreaterThanOrEqual(127, $color[1]);
		$this->assertGreaterThanOrEqual(127, $color[2]);
	}

	public function testMakeSection(){
		$ingest = new Ingest(new PDO('sqlite:test.sqlite'), $this->makeTestData());
		$color = $this->invokeMethod($ingest, 'makeNewSection', []);
	}

	private function makeTestData(){
		$a = ["allCourses" => [], "preregistered" => [], "timePref" => "", "fullClasses" => false,
			"startTime" => "8:00am", "endTime" => "10:00pm", "unwantedTimes" => []];
		return json_encode($a);
	}
}
