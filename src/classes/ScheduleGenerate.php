<?php

class ScheduleGenerate{
	/** @var Ingest $ingest */
	private $ingest;
	/** @var \LimitedMinHeap $schedules */
	private $schedules;
	/** @var int $numSchedules */
	private $numSchedules = 0;
	/** @var int $sectionCount */
	private $sectionCount = 0;

	/**
	 * ScheduleGenerate constructor.
	 *
	 * @param Ingest $ingest
	 */
	public function __construct($ingest){
		$this->ingest = $ingest;
		$this->schedules = new LimitedMinHeap();
	}

	/**
	 * Generates an RGB string from a given array of color values
	 *
	 * @param array|int $color
	 * @return string
	 */
	private function makeColorString($color){
		return $color[0].", ".$color[1].", ".$color[2];
	}

	/**
	 * Generate all schedules
	 */
	public function generateSchedules(){
		$allSections = $this->ingest->getAllSections();
		$this->sectionCount = count($allSections);

		foreach($allSections as $k=>$v){
			unset($allSections[$k]);
			if(!isset($v->meetingTime)){
				continue;
			}
			$curr = [];
			$this->run($allSections, $curr, $v);
		}
	}

	/**
	 * Generate all schedules recursively
	 *
	 * @param array|Section $sections list of all sections that can be added
	 * @param array|Section $curr current list of sections in the schedule being generated
	 * @param Section $pick
	 */
	private function run($sections, $curr, $pick){
		$curr[] =  $pick;
		$temp = $sections;
		foreach($temp as $k=>$v){
			if($v->conflictsWith($pick)){
				unset($temp[$k]);
			}
		}
		if(count($temp) == 0){
			$a = new Schedule();
			$requiredCourses = 0;
			foreach($curr as $b){
				if($b->isRequiredCourse()){
					$requiredCourses++;
				}
				$a->addSection($b);
			}
			if($requiredCourses == $this->ingest->getRequiredCourseNum()){
				$a->setScore($this->ingest->getMorning());
				$this->schedules->insert($a);
				$this->numSchedules += 1;
			}
		}
		else{
			foreach($temp as $k=>$v){
				unset($temp[$k]);
				$this->run($temp, $curr, $v);
			}
		}
	}

	/**
	 * Returns the correctly pluralized version of the given word
	 *
	 * @param string $word singular form of the word to be pluralized (or kept singular)
	 * @param int $num number that the word is referring to
	 * @return string
	 */
	public function plural($word, $num){
		if($num == 1){
			return $word;
		}
		else{
			if(substr($word, -1) == "y"){
				return substr($word, 0, strlen($word)-1)."ies";
			}
			else if(substr($word, -1) == "s"){
				return $word."es";
			}
			else{
				return $word."s";
			}
		}
	}

	/**
	 * Makes array of two arrays. The first is for the calendar view and the second is for the list view
	 *
	 * @param array|Schedule $schedules
	 * @return array
	 */
	public function makeDataForCalendarAndList($schedules){
		$weekSchedule = [];
		$listSchedule = [];
		$num = 0;
		foreach($schedules as $a){
			$cpd = $a->getCPD();
			$daysString = [];

			$numDays = $a->getLastTime()[0] - $a->getFirstTime()[0] + 1;
			for($i = $a->getFirstTime()[0]; $i < ($numDays + $a->getFirstTime()[0]); $i++){
				$daysString[] = Schedule::intToDay($i);
			}

			$crnList = "";
			$listRows = [];
			foreach($a->getSchedule() as $b){
				/** @var Section $b */
				foreach($b->getCRN() as $crn){
					if($b->preregistered){
						$crn = "<em>".$crn."</em>";
					}
					$crnList = $crnList.", ".$crn;
				}
				$crns = $b->getCRN()[0];
				foreach($b->getCRN() as $j => $crn){
					if($j == 0){
						continue;
					}
					$crns = $crns.", ".$crn;
				}
				$listRows[] = ["color" => $this->makeColorString($b->getColor()), "crns" => $crns,
					"coursenum" =>  $b->getCourseNumber(), "fos" => $b->getFieldOfStudy(),
					"preregistered" => $b->preregistered, "prof" => $b->getProf(),
					"title" => $b->getCourseTitle(), "titleWithDate" => $b->__toString()];
			}

			$listSchedule[intval($num / 4)][] = ["rows" => $listRows, "collapse" => $num >= 4, "num" => $num,
				"hasAllClasses" => $this->ingest->getClassCount() == $a->getNumClasses(),
				"numUnits" => ["num" => $a->getNumUnits(), "string" => $this->plural("unit", $a->getNumUnits())],
				"numClasses" => ["num" => $a->getNumClasses(), "string" => $this->plural("class", $a->getNumClasses())],
				"numCPD" => ["num" => reset($cpd), "string" => $this->plural("class", reset($cpd))],
				"dayCPD" => key($cpd), "crnList" => substr($crnList, 2)
			];

			$timeArray = [];
			foreach($a->getSchedule() as $b){
				foreach($b->meetingTime as $day => $times){
					foreach($times as $time){
						$timeArray[$time["from"]][$day] = $b;
					}
				}
			}
			ksort($timeArray);

			$rows = [];
			$rowCount = 0;
			foreach($timeArray as $k2 => $v){
				$rows[$rowCount] = [];
				$rows[$rowCount]["timestamp"] = date("g:i a", $k2);
				$rows[$rowCount]["rowData"] = [];

				for($i = $a->getFirstTime()[0]; $i < ($numDays + $a->getFirstTime()[0]); $i++){
					/** @var array|\Section $v */
					if(isset($v[Schedule::intToDay($i)])){
						$crns = $v[Schedule::intToDay($i)]->getCRN()[0];
						foreach($v[Schedule::intToDay($i)]->getCRN() as $j => $crn){
							if($j == 0){
								continue;
							}
							$crns = $crns.", ".$crn;
						}
						$rows[$rowCount]["rowData"][] = ["color" => $this->makeColorString($v[Schedule::intToDay($i)]->getColor()),
							"crns" => $crns, "coursenum" => $v[Schedule::intToDay($i)]->getCourseNumber(),
							"fos" => $v[Schedule::intToDay($i)]->getFieldOfStudy(),
							"preregistered" => $v[Schedule::intToDay($i)]->preregistered,
							"title" => $v[Schedule::intToDay($i)]->getCourseTitle(), "prof" => $v[Schedule::intToDay($i)]->getProf()];
					}else{
						$rows[$rowCount]["rowData"][] = ["empty" => true];
					}
				}
				$rowCount += 1;
			}

			$weekSchedule[intval($num / 2)][] = ["rows" => $rows, "daysString" => $daysString,
				"hasAllClasses" => $this->ingest->getClassCount() == $a->getNumClasses(),
				"numUnits" => ["num" => $a->getNumUnits(), "string" => $this->plural("unit", $a->getNumUnits())],
				"numClasses" => ["num" => $a->getNumClasses(), "string" => $this->plural("class", $a->getNumClasses())],
				"numCPD" => ["num" => reset($cpd), "string" => $this->plural("class", reset($cpd))],
				"dayCPD" => key($cpd), "num" => $num, "crnList" => substr($crnList, 2)
			];

			$num += 1;
		}

		return [$weekSchedule, $listSchedule];
	}

	/**
	 * @return \LimitedMinHeap
	 */
	public function getSchedules(){
		return $this->schedules;
	}

	/**
	 * @return int
	 */
	public function getNumSchedules(){
		return $this->numSchedules;
	}

	/**
	 * @return int
	 */
	public function getSectionCount(){
		return $this->sectionCount;
	}

}
