<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 6/10/2017
 * Time: 10:37 AM
 */

use PHPUnit\Framework\TestCase;
require_once(__DIR__."/../src/config.php");

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
	public function invokeMethod(&$object, $methodName, array $parameters = [])
	{
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}

	public function testColorGenerator(){
		$ingest = new Ingest(new FakeDAL(), $this->makeTestData());

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
		$ingest = new Ingest(new FakeDAL(), $this->makeTestData());
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

	public function testMakeUnwantedTimes(){
		$ingest = new Ingest(new FakeDAL(), $this->makeTestData());
		$days = $this->invokeMethod($ingest, 'setDaysWithUnwantedTimes', [[]]);
		$this->assertEmpty($days);
		$days = $this->invokeMethod($ingest, 'setDaysWithUnwantedTimes', [[["M" => ["from", "to"]]]]);
		$this->assertEquals(["Monday"], $days);
		$days = $this->invokeMethod($ingest, 'setDaysWithUnwantedTimes', [
			[["M" => ["from", "to"]], ["T" => ["from",  "to"]]]
		]);
		$this->assertEquals(["Monday", "Tuesday"], $days);
		$days = $this->invokeMethod($ingest, 'setDaysWithUnwantedTimes', [
			[["R" => ["from", "to"]], ["R" => ["from",  "to"]]]
		]);
		$this->assertEquals(["Thursday"], $days);
		$days = $this->invokeMethod($ingest, 'setDaysWithUnwantedTimes', [
			[["R" => ["from", "to"]], ["T" => ["from",  "to"]]]
		]);
		$this->assertEquals(["Thursday", "Tuesday"], $days);
	}

	public function testGeneratePreregisteredSections(){
		$ingest = new Ingest(new FakeDAL(), $this->makeTestData());
		$sections = $this->invokeMethod($ingest, 'generatePreregisteredSections', [["123456"]]);
		/** @var $sections array|Section */
		$this->assertEquals(1, count($sections));
		$this->assertEquals("prereg", $sections[0]->getCourseTitle());

		$sections = $this->invokeMethod($ingest, 'generatePreregisteredSections', [["123456", "123457"]]);
		/** @var $sections array|Section */
		$this->assertEquals(2, count($sections));
		$this->assertEquals("prereg", $sections[0]->getCourseTitle());
		$this->assertEquals("prereg2", $sections[1]->getCourseTitle());
		$this->assertEquals([1, strtotime("0930")], $sections[1]->getEarliestTime());
		$this->assertEquals([0, strtotime("1030")], $sections[1]->getFirstTime());
		$this->assertEquals([0, strtotime("1330")], $sections[1]->getLatestTime());
		$this->assertEquals([2, strtotime("1330")], $sections[1]->getLastTime());

		$sections = $this->invokeMethod($ingest, 'generatePreregisteredSections', [["XXX"]]);
		$this->assertEquals(0, count($sections));
	}

	public function testRemoveSectionsForTime(){
		$ingest = new Ingest(new FakeDAL(), $this->makeTestData());
		$section1 = new Section("Title", "Subj", 100, 1, ["123456"]);
		$section1->addTime("M", "0800", "0930");
		$section1->addTime("W", "0800", "0930");
		$section1->addTime("F", "0800", "0930");
		$section2 = new Section("Title2", "Subj2", 101, 1, ["123457"]);
		$section2->addTime("R", "1600", "1730");

		$allSections = [$section1, $section2];
		$sections = $this->invokeMethod($ingest, 'removeSectionsForTime', [
			$allSections, strtotime("0800"), strtotime("1800"), [], []
		]);
		$this->assertEquals($allSections, $sections);

		$sections = $this->invokeMethod($ingest, 'removeSectionsForTime', [
			$allSections, strtotime("0800"), strtotime("1800"), [0], []
		]);
		$this->assertEquals($allSections, $sections);

		$sections = $this->invokeMethod($ingest, 'removeSectionsForTime', [
			$allSections, strtotime("0800"), strtotime("1800"), [0], [["startTime" => "9:30 AM","endTime" =>
				"12:00 PM","M"=>"M"]]
		]);
		$this->assertEquals($allSections, $sections);

		$sections = $this->invokeMethod($ingest, 'removeSectionsForTime', [
			$allSections, strtotime("0800"), strtotime("1800"), [0], [["startTime" => "7:30 AM","endTime" =>
			"12:00 PM","M"=>"M"]]
		]);
		$this->assertEquals(1, count($sections));
		$this->assertEquals([1 => $section2], $sections);

		$sections = $this->invokeMethod($ingest, 'removeSectionsForTime', [
			$allSections, strtotime("0800"), strtotime("1800"), [0, 4], [["startTime" => "7:30 AM","endTime" =>
				"12:00 PM","M"=>"M"], ["startTime" => "3:30 PM","endTime" => "5:00 PM","R"=>"R"]]
		]);
		$this->assertEquals(0, count($sections));
		$this->assertEquals([], $sections);

		$sections = $this->invokeMethod($ingest, 'removeSectionsForTime', [
			$allSections, strtotime("1030"), strtotime("1800"), [], []
		]);
		$this->assertEquals(1, count($sections));
		$this->assertEquals([1 => $section2], $sections);
	}

	public function testGenerateSections(){
		$ingest = new Ingest(new FakeDAL(), $this->makeTestData());
		$this->invokeMethod($ingest, 'generateSections', [
			
		]);
	}

	private function makeTestData(){
		$a = ["allCourses" => [], "preregistered" => [], "timePref" => "", "fullClasses" => false,
			"startTime" => "8:00am", "endTime" => "10:00pm", "unwantedTimes" => []];
		return json_encode($a);
	}
}

class FakeDAL extends MySQLDAL{
	public function __construct(){
	}

	public function fetchByCRN($crn){
		if($crn == "123456"){
			return [[COLUMNS_COURSE_TITLE => "prereg", COLUMNS_FOS => "MyFOS", COLUMNS_COURSE_NUM => "100",
				COLUMNS_UNITS => 1, COLUMNS_CRN => "123456", COLUMNS_TIME_BEGIN => "1030", COLUMNS_TIME_END => "1330",
				COLUMNS_PROF_FN => "FirstName", COLUMNS_PROF_LN => "LastName", "M" => "M", "W" => "W"
			]];
		}
		else if($crn == "123457"){
			return [[COLUMNS_COURSE_TITLE => "prereg2", COLUMNS_FOS => "MyFOS", COLUMNS_COURSE_NUM => "100",
				COLUMNS_UNITS => 1, COLUMNS_CRN => "123457", COLUMNS_TIME_BEGIN => "1030", COLUMNS_TIME_END => "1330",
				COLUMNS_PROF_FN => "FirstName", COLUMNS_PROF_LN => "LastName", "M" => "M", "W" => "W"
				],
				[COLUMNS_COURSE_TITLE => "prereg2", COLUMNS_FOS => "MyFOS", COLUMNS_COURSE_NUM => "100",
					COLUMNS_UNITS => 1, COLUMNS_CRN => "123457", COLUMNS_TIME_BEGIN => "0930", COLUMNS_TIME_END => "1130",
					COLUMNS_PROF_FN => "FirstName", COLUMNS_PROF_LN => "LastName", "T" => "T",
				]
			];
		}
		else{
			return null;
		}
	}
}
