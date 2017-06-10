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
		require_once(__DIR__."/../src/config.php");
		$ingest = new Ingest(new PDO('sqlite:test.sqlite'), $this->makeTestData());
		$newSection = $this->invokeMethod($ingest, 'makeNewSection', [
			["requiredCourse" => true],
			"Course Title",
			[   COLUMNS_FOS => "MyFOS", COLUMNS_COURSE_NUM => "100", COLUMNS_UNITS => 1, COLUMNS_CRN => "123456",
				COLUMNS_TIME_BEGIN => "1030", COLUMNS_TIME_END => "1330", COLUMNS_PROF_FN => "FirstName",
				COLUMNS_PROF_LN => "LastName", "M" => "M", "W" => "W"
			]
		]);
		/** @var $newSection Section */
		$this->assertFalse($newSection->preregistered);
		$this->assertFalse($newSection->meetsFriday());
		$this->assertEquals(1, $newSection->getNumUnits());
		$this->assertEquals(["123456"], $newSection->getCRN());
		$this->assertTrue($newSection->isRequiredCourse());
		$this->assertEquals("Course Title", $newSection->getCourseTitle());
		$this->assertEquals("100", $newSection->getCourseNumber());
		$this->assertEquals("MyFOS", $newSection->getFieldOfStudy());
		$this->assertEquals([0, strtotime("1030")], $newSection->getFirstTime());
		$this->assertEquals([2, strtotime("1330")], $newSection->getLastTime());
		$this->assertEquals([0, strtotime("1030")], $newSection->getEarliestTime());
		$this->assertEquals([0, strtotime("1330")], $newSection->getLatestTime());
		$this->assertEquals("FirstName LastName", $newSection->getProf());
	}

	public function testMakeMultipleSections(){
		require_once(__DIR__."/../src/config.php");
		$ingest = new Ingest(new PDO('sqlite:test.sqlite'), $this->makeTestData());
		$newSection1 = $this->invokeMethod($ingest, 'makeNewSection', [
			["requiredCourse" => true],
			"Course Title",
			[   COLUMNS_FOS => "MyFOS", COLUMNS_COURSE_NUM => "100", COLUMNS_UNITS => 1, COLUMNS_CRN => "123456",
				COLUMNS_TIME_BEGIN => "1030", COLUMNS_TIME_END => "1330", COLUMNS_PROF_FN => "FirstName",
				COLUMNS_PROF_LN => "LastName", "M" => "M", "W" => "W"
			]
		]);
		/** @var $newSection1 Section */
		$newSection2 = $this->invokeMethod($ingest, 'makeNewSection', [
			["requiredCourse" => true],
			"Course Title",
			[   COLUMNS_FOS => "MyFOS", COLUMNS_COURSE_NUM => "100", COLUMNS_UNITS => 1, COLUMNS_CRN => "123457",
				COLUMNS_TIME_BEGIN => "1030", COLUMNS_TIME_END => "1330", COLUMNS_PROF_FN => "FirstName",
				COLUMNS_PROF_LN => "LastName", "M" => "M", "W" => "W"
			]
		]);
		/** @var $newSection2 Section */

	}

	private function makeTestData(){
		$a = ["allCourses" => [], "preregistered" => [], "timePref" => "", "fullClasses" => false,
			"startTime" => "8:00am", "endTime" => "10:00pm", "unwantedTimes" => []];
		return json_encode($a);
	}
}
