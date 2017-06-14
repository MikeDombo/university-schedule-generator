<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 6/13/2017
 * Time: 10:27 PM
 */

use PHPUnit\Framework\TestCase;

class ScheduleGenerateTest extends TestCase{

	public function testConstructor(){
		$ingest = new Ingest(new FakeDAL(), IngestTest::makeTestData());
		$scheduleGenerate = new ScheduleGenerate($ingest);
		$this->assertEquals(0, $scheduleGenerate->getNumSchedules());
		$this->assertEquals(0, $scheduleGenerate->getSectionCount());
	}

	public function testGenerateSchedules(){
		$courseData = "{\"allCourses\":[{\"CourseNum\":121,\"FOS\":\"LAIS\",\"Title\":\"Intensive Elementary Spanish\"}],\"timePref\":false,\"fullClasses\":true,\"preregistered\":[\"\"],\"startTime\":\"8:00 AM\",\"endTime\":\"10:00 PM\",\"unwantedTimes\":[]}";
		$ingest = new Ingest(new FakeDAL(), $courseData);
		$ingest->generateSections();
		$scheduleGenerate = new ScheduleGenerate($ingest);

		$this->assertEquals(0, $scheduleGenerate->getNumSchedules());
		$this->assertEquals(0, $scheduleGenerate->getSectionCount());

		$scheduleGenerate->generateSchedules($ingest->getAllSections());

		$this->assertEquals(2, $scheduleGenerate->getSectionCount());
		$this->assertEquals(2, $scheduleGenerate->getNumSchedules());

		$schedule1 = new Schedule();
		$schedule1->addSection($ingest->getAllSections()[0]);
		$schedule1->setScore(false);

		$schedule2 = new Schedule();
		$schedule2->addSection($ingest->getAllSections()[1]);
		$schedule2->setScore(false);

		$this->assertEquals([$schedule1, $schedule2], $scheduleGenerate->getSchedules()->getMaxArray());
	}

	public function testGenerateSchedulesWithRequired(){
		$courseData = "{\"allCourses\":[{\"CourseNum\":121,\"FOS\":\"LAIS\",\"Title\":\"Intensive Elementary Spanish\",\"displayTitle\":\"Intensive Elementary Spanish\"},{\"CourseNum\":323,\"FOS\":\"CMSC\",\"Title\":\"Design and Implementation of Programming Languages\",\"requiredCourse\":true}],\"timePref\":false,\"fullClasses\":true,\"preregistered\":[\"\"],\"startTime\":\"8:00 AM\",\"endTime\":\"10:00 PM\",\"unwantedTimes\":[]}";
		$ingest = new Ingest(new FakeDAL(), $courseData);
		$ingest->generateSections();
		$scheduleGenerate = new ScheduleGenerate($ingest);
		$scheduleGenerate->generateSchedules($ingest->getAllSections());

		$this->assertEquals(3, $scheduleGenerate->getSectionCount());
		$this->assertEquals(3, $scheduleGenerate->getNumSchedules());

		$schedule1 = new Schedule();
		$schedule1->addSection($ingest->getAllSections()[0]);
		$schedule1->addSection($ingest->getAllSections()[2]);
		$schedule1->setScore(false);

		$schedule2 = new Schedule();
		$schedule2->addSection($ingest->getAllSections()[1]);
		$schedule2->addSection($ingest->getAllSections()[2]);
		$schedule2->setScore(false);

		$schedule3 = new Schedule();
		$schedule3->addSection($ingest->getAllSections()[2]);
		$schedule3->setScore(false);

		$this->assertEquals([$schedule1, $schedule2, $schedule3], $scheduleGenerate->getSchedules()->getMaxArray());
	}

	public function testPlural(){
		$this->assertEquals("classes", ScheduleGenerate::plural("class", 0));
		$this->assertEquals("Classes", ScheduleGenerate::plural("Class", 0));
		$this->assertEquals("class", ScheduleGenerate::plural("class", 1));
		$this->assertEquals("Class", ScheduleGenerate::plural("Class", 1));
		$this->assertEquals("classes", ScheduleGenerate::plural("class", -1));
		$this->assertEquals("classes", ScheduleGenerate::plural("class", 2));

		$this->assertEquals("batteries", ScheduleGenerate::plural("battery", 0));
		$this->assertEquals("battery", ScheduleGenerate::plural("battery", 1));

		$this->assertEquals("days", ScheduleGenerate::plural("day", 0));
		$this->assertEquals("day", ScheduleGenerate::plural("day", 1));

		$this->assertEquals("potatoes", ScheduleGenerate::plural("potato", 0));
		$this->assertEquals("potato", ScheduleGenerate::plural("potato", 1));

		$this->assertEquals("features", ScheduleGenerate::plural("feature", 0));
		$this->assertEquals("feature", ScheduleGenerate::plural("feature", 1));
	}

	public function testMakeColorString(){
		$ingest = new Ingest(new FakeDAL(), IngestTest::makeTestData());
		$scheduleGenerate = new ScheduleGenerate($ingest);
		$color = IngestTest::invokeMethod($scheduleGenerate, 'makeColorString', [[0, 0, 0]]);
		$this->assertEquals("0, 0, 0", $color);
	}
}
