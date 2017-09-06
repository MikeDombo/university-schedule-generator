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
	public static function invokeMethod(&$object, $methodName, array $parameters = [])
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
		$days = $this->invokeMethod($ingest, 'makeDaysWithUnwantedTimes', [[]]);
		$this->assertEmpty($days);
		$days = $this->invokeMethod($ingest, 'makeDaysWithUnwantedTimes', [[["M" => ["from", "to"]]]]);
		$this->assertEquals(["Monday"], $days);
		$days = $this->invokeMethod($ingest, 'makeDaysWithUnwantedTimes', [
			[["M" => ["from", "to"]], ["T" => ["from",  "to"]]]
		]);
		$this->assertEquals(["Monday", "Tuesday"], $days);
		$days = $this->invokeMethod($ingest, 'makeDaysWithUnwantedTimes', [
			[["R" => ["from", "to"]], ["R" => ["from",  "to"]]]
		]);
		$this->assertEquals(["Thursday"], $days);
		$days = $this->invokeMethod($ingest, 'makeDaysWithUnwantedTimes', [
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
		$section1 = new Section(0, "Title", "Subj", 100, 1, ["123456"]);
		$section1->addTime("M", "0800", "0930");
		$section1->addTime("W", "0800", "0930");
		$section1->addTime("F", "0800", "0930");
		$section2 = new Section(0, "Title2", "Subj2", 101, 1, ["123457"]);
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

	public function testGenerateSectionsSingleCourse(){
		$courseData1 = "{\"allCourses\":[{\"CourseNum\":323,\"FOS\":\"CMSC\",\"Title\":\"Design and Implementation of Programming Languages\"}],\"timePref\":false,\"fullClasses\":true,\"preregistered\":[\"\"],\"startTime\":\"8:00 AM\",\"endTime\":\"10:00 PM\",\"unwantedTimes\":[]}";
		$ingest = new Ingest(new FakeDAL(), $courseData1);
		$ingest->generateSections();
		$section1 = new Section(0, "Design and Implementation of Programming Languages", "CMSC", "323", 1.0, ["10006"]);
		$section1->addTime("F", "0900", "1015");
		$section1->addTime("M", "0900", "1015");
		$section1->addTime("W", "0900", "1015");
		$section1->setProf(" Charlesworth");
		$this->assertEquals(1, count($ingest->getAllSections()));
		$this->assertTrue($this->compareSections($section1, $ingest->getAllSections()[0]));
	}

	public function testGenerateSectionsMultipleCourses(){
		$courseData = "{\"allCourses\":[{\"CourseNum\":323,\"FOS\":\"CMSC\",\"Title\":\"Design and Implementation of Programming Languages\",\"displayTitle\":\"Design and Implementation of Programming Languages\"},{\"CourseNum\":331,\"FOS\":\"CMSC\",\"Title\":\"Introduction to Compiler Construction\"}],\"timePref\":false,\"fullClasses\":true,\"preregistered\":[\"\"],\"startTime\":\"8:00 AM\",\"endTime\":\"10:00 PM\",\"unwantedTimes\":[]}";
		$ingest = new Ingest(new FakeDAL(), $courseData);
		$ingest->generateSections();

		$section1 = new Section(0, "Design and Implementation of Programming Languages", "CMSC", "323", 1.0, ["10006"]);
		$section1->addTime("F", "0900", "1015");
		$section1->addTime("M", "0900", "1015");
		$section1->addTime("W", "0900", "1015");
		$section1->setProf(" Charlesworth");

		$section2 = new Section(0, "Introduction to Compiler Construction", "CMSC", "331", 1.0, ["17069"]);
		$section2->addTime("W", "1500", "1550");
		$section2->addTime("T", "1200", "1315");
		$section2->addTime("R", "1200", "1315");
		$section2->setProf(" Szajda");
		$mySections = [$section1, $section2];

		$this->assertEquals(2, count($ingest->getAllSections()));
		$this->assertEquals(2, count($mySections));
		foreach($mySections as $k=>$v){
			$this->assertTrue($this->compareSections($v, $ingest->getAllSections()[$k]));
		}

		$courseData = "{\"allCourses\":[{\"CourseNum\":323,\"FOS\":\"CMSC\",\"Title\":\"Design and Implementation of Programming Languages\",\"displayTitle\":\"Design and Implementation of Programming Languages\"},{\"CourseNum\":331,\"FOS\":\"CMSC\",\"Title\":\"Introduction to Compiler Construction\",\"displayTitle\":\"Introduction to Compiler Construction\"},{\"CourseNum\":326,\"FOS\":\"CHEM\",\"Title\":\"Biochemistry\"}],\"timePref\":false,\"fullClasses\":true,\"preregistered\":[\"\"],\"startTime\":\"8:00 AM\",\"endTime\":\"10:00 PM\",\"unwantedTimes\":[]}";
		$ingest = new Ingest(new FakeDAL(), $courseData);
		$this->invokeMethod($ingest, 'generateSections', []);
		$section3 = new Section(0, "Biochemistry", "CHEM", "326", 1.0, ["10302"]);
		$section3->addTime("M", "0900", "1000");
		$section3->addTime("W", "0900", "1000");
		$section3->addTime("F", "0900", "1000");
		$section3->setProf(" Hamm");

		$section4 = new Section(0, "Biochemistry", "CHEM", "326", 1.0, ["16465"]);
		$section4->addTime("M", "1030", "1130");
		$section4->addTime("W", "1030", "1130");
		$section4->addTime("F", "1030", "1130");
		$section4->setProf(" Hamm");
		$mySections[] = $section3;
		$mySections[] = $section4;

		$this->assertEquals(4, count($ingest->getAllSections()));
		$this->assertEquals(4, count($mySections));
		foreach($mySections as $k=>$v){
			$this->assertTrue($this->compareSections($v, $ingest->getAllSections()[$k]));
		}
	}

	public function testGenerateSectionsMultipleOptions(){
		$courseData = "{\"allCourses\":[{\"CourseNum\":121,\"FOS\":\"LAIS\",\"Title\":\"Intensive Elementary Spanish\"}],\"timePref\":false,\"fullClasses\":true,\"preregistered\":[\"\"],\"startTime\":\"8:00 AM\",\"endTime\":\"10:00 PM\",\"unwantedTimes\":[]}";
		$ingest = new Ingest(new FakeDAL(), $courseData);
		$ingest->generateSections();

		$section1 = new Section(0, "Intensive Elementary Spanish", "LAIS", "121", 2.0, ["11254", "11311"]);
		$section1->addTime("M", "1630", "1715");
		$section1->addTime("W", "1630", "1715");
		$section1->addTime("T", "1030", "1145");
		$section1->addTime("R", "1030", "1145");
		$section1->addTime("M", "1030", "1120");
		$section1->addTime("W", "1030", "1120");
		$section1->addTime("F", "1030", "1120");
		$section1->setMultiples(true);

		$section2 = new Section(0, "Intensive Elementary Spanish", "LAIS", "121", 2.0, ["11254", "11256"]);
		$section2->addTime("T", "0900", "0945");
		$section2->addTime("R", "0900", "0945");
		$section2->addTime("T", "1030", "1145");
		$section2->addTime("R", "1030", "1145");
		$section2->addTime("M", "1030", "1120");
		$section2->addTime("W", "1030", "1120");
		$section2->addTime("F", "1030", "1120");
		$section2->setMultiples(true);

		$mySections = [$section1, $section2];

		$this->assertEquals(2, count($ingest->getAllSections()));
		$this->assertEquals(2, count($mySections));
		foreach($mySections as $k=>$v){
			$this->assertTrue($this->compareSections($v, $ingest->getAllSections()[$k]));
		}
	}

	public function testGenerateSectionsRequiredCourse(){
		$courseData = "{\"allCourses\":[{\"CourseNum\":121,\"FOS\":\"LAIS\",\"Title\":\"Intensive Elementary Spanish\",\"displayTitle\":\"Intensive Elementary Spanish\"},{\"CourseNum\":323,\"FOS\":\"CMSC\",\"Title\":\"Design and Implementation of Programming Languages\",\"requiredCourse\":true}],\"timePref\":false,\"fullClasses\":true,\"preregistered\":[\"\"],\"startTime\":\"8:00 AM\",\"endTime\":\"10:00 PM\",\"unwantedTimes\":[]}";
		$ingest = new Ingest(new FakeDAL(), $courseData);
		$ingest->generateSections();

		$section1 = new Section(0, "Design and Implementation of Programming Languages", "CMSC", "323", 1.0, ["10006"]);
		$section1->addTime("F", "0900", "1015");
		$section1->addTime("M", "0900", "1015");
		$section1->addTime("W", "0900", "1015");
		$section1->setProf(" Charlesworth");
		$section1->setRequiredCourse(true);

		$section2 = new Section(0, "Intensive Elementary Spanish", "LAIS", "121", 2.0, ["11254", "11311"]);
		$section2->addTime("M", "1630", "1715");
		$section2->addTime("W", "1630", "1715");
		$section2->addTime("T", "1030", "1145");
		$section2->addTime("R", "1030", "1145");
		$section2->addTime("M", "1030", "1120");
		$section2->addTime("W", "1030", "1120");
		$section2->addTime("F", "1030", "1120");
		$section2->setMultiples(true);

		$section3 = new Section(0, "Intensive Elementary Spanish", "LAIS", "121", 2.0, ["11254", "11256"]);
		$section3->addTime("T", "0900", "0945");
		$section3->addTime("R", "0900", "0945");
		$section3->addTime("T", "1030", "1145");
		$section3->addTime("R", "1030", "1145");
		$section3->addTime("M", "1030", "1120");
		$section3->addTime("W", "1030", "1120");
		$section3->addTime("F", "1030", "1120");
		$section3->setMultiples(true);
		$mySections = [$section2, $section3, $section1];

		$this->assertEquals(3, count($ingest->getAllSections()));
		$this->assertEquals(3, count($mySections));
		foreach($mySections as $k=>$v){
			$this->assertTrue($this->compareSections($v, $ingest->getAllSections()[$k]));
		}
	}

	private function compareSections(Section $a, Section $b){
		if($a->getProf() != $b->getProf()){
			return false;
		}
		else if($a->getLatestTime() != $b->getLatestTime()){
			return false;
		}
		else if($a->getLastTime() != $b->getLastTime()){
			return false;
		}
		else if($a->getEarliestTime() != $b->getEarliestTime()){
			return false;
		}
		else if($a->getFirstTime() != $b->getFirstTime()){
			return false;
		}
		else if($a->getCourseTitle() != $b->getCourseTitle()){
			return false;
		}
		else if($a->getFieldOfStudy() != $b->getFieldOfStudy()){
			return false;
		}
		else if($a->getCourseNumber() != $b->getCourseNumber()){
			return false;
		}
		else if($a->getCRN() != $b->getCRN()){
			return false;
		}
		else if($a->getNumUnits() != $b->getNumUnits()){
			return false;
		}
		else if($a->isRequiredCourse() != $b->isRequiredCourse()){
			return false;
		}
		else if($a->preregistered != $b->preregistered){
			return false;
		}
		return true;
	}

	public static function makeTestData(){
		$a = ["allCourses" => [], "preregistered" => [], "timePref" => "", "fullClasses" => false,
			"startTime" => "8:00am", "endTime" => "10:00pm", "unwantedTimes" => []];
		return json_encode($a);
	}
}

class FakeDAL extends MySQLDAL{
	public function __construct(){
	}

	public function fetchBySubjAndNumber($num, $subj){
		if($num == "323" && $subj == "CMSC"){
			return json_decode('[{"ID":"336","CRN":"10006","SUBJ":"CMSC","CRSE":"323","SEC":"01","TITLE":"DSGN\/IMPLEMNTN PROG LANG W\/LAB","M":"","T":"","W":"","R":"","F":"F","BEGIN":"0900","END":"1015","LASTNAME":"Charlesworth","BLDG":"JPSN","ROOM":"120","MAX":"18","GMOD":"S","ARPVL":"","UNITS":"1","ENROLLMENT":"17"},{"ID":"337","CRN":"10006","SUBJ":"CMSC","CRSE":"323","SEC":"01","TITLE":"DSGN\/IMPLEMNTN PROG LANG W\/LAB","M":"M","T":"","W":"W","R":"","F":"","BEGIN":"0900","END":"1015","LASTNAME":"Charlesworth","BLDG":"JPSN","ROOM":"120","MAX":"18","GMOD":"S","ARPVL":"","UNITS":"1","ENROLLMENT":"17"}]', true);
		}
		else if($num == "331" && $subj == "CMSC"){
			return json_decode('[{"ID":"338","CRN":"17069","SUBJ":"CMSC","CRSE":"331","SEC":"01","TITLE":"INTRO TO COMPLR CNSTRCTN W\/LAB","M":"","T":"","W":"W","R":"","F":"","BEGIN":"1500","END":"1550","LASTNAME":"Szajda","BLDG":"JPSN","ROOM":"G22","MAX":"18","GMOD":"S","ARPVL":"","UNITS":"1","ENROLLMENT":"8"},{"ID":"339","CRN":"17069","SUBJ":"CMSC","CRSE":"331","SEC":"01","TITLE":"INTRO TO COMPLR CNSTRCTN W\/LAB","M":"","T":"T","W":"","R":"R","F":"","BEGIN":"1200","END":"1315","LASTNAME":"Szajda","BLDG":"JPSN","ROOM":"231","MAX":"18","GMOD":"S","ARPVL":"","UNITS":"1","ENROLLMENT":"8"}]', true);
		}
		else if($num == "326" && $subj == "CHEM"){
			return json_decode('[{"ID":"276","CRN":"10302","SUBJ":"CHEM","CRSE":"326","SEC":"01","TITLE":"BIOCHEMISTRY","M":"M","T":"","W":"W","R":"","F":"F","BEGIN":"0900","END":"1000","LASTNAME":"Hamm","BLDG":"GOTW","ROOM":"E303","MAX":"25","GMOD":"S","ARPVL":"","UNITS":"1","ENROLLMENT":"7"},{"ID":"277","CRN":"16465","SUBJ":"CHEM","CRSE":"326","SEC":"02","TITLE":"BIOCHEMISTRY","M":"M","T":"","W":"W","R":"","F":"F","BEGIN":"1030","END":"1130","LASTNAME":"Hamm","BLDG":"GOTW","ROOM":"E303","MAX":"25","GMOD":"S","ARPVL":"","UNITS":"1","ENROLLMENT":"15"}]', true);
		}
		else if($num == "121" && $subj == "LAIS"){
			return json_decode('[{"ID":"721","CRN":"11254","SUBJ":"LAIS","CRSE":"121","SEC":"01","TITLE":"INTENSIVE ELEM SPAN W\/ DRILL","M":"","T":"T","W":"","R":"R","F":"","BEGIN":"1030","END":"1145","LASTNAME":"Corradini","BLDG":"PURH","ROOM":"G13","MAX":"20","GMOD":"S","ARPVL":"DP","UNITS":"2","ENROLLMENT":""},{"ID":"722","CRN":"11254","SUBJ":"LAIS","CRSE":"121","SEC":"01","TITLE":"INTENSIVE ELEM SPAN W\/ DRILL","M":"M","T":"","W":"W","R":"","F":"F","BEGIN":"1030","END":"1120","LASTNAME":"Corradini","BLDG":"PURH","ROOM":"G13","MAX":"20","GMOD":"S","ARPVL":"DP","UNITS":"2","ENROLLMENT":""},{"ID":"723","CRN":"11311","SUBJ":"LAIS","CRSE":"121","SEC":"D1A","TITLE":"INTENSIVE ELEM SPANISH DRILL","M":"M","T":"","W":"W","R":"","F":"","BEGIN":"1630","END":"1715","LASTNAME":"Corradini","BLDG":"","ROOM":"","MAX":"10","GMOD":"S","ARPVL":"","UNITS":"","ENROLLMENT":""},{"ID":"724","CRN":"11256","SUBJ":"LAIS","CRSE":"121","SEC":"D1B","TITLE":"INTENSIVE ELEM SPANISH DRILL","M":"","T":"T","W":"","R":"R","F":"","BEGIN":"0900","END":"0945","LASTNAME":"Corradini","BLDG":"","ROOM":"","MAX":"10","GMOD":"S","ARPVL":"","UNITS":"","ENROLLMENT":""}]', true);
		}
		return null;
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
